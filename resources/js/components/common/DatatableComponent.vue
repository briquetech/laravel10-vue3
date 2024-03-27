<template>
	<div class="row mb-1 d-flex align-items-center">
		<!--  v-if="!dataprops.search || dataprops.search=='simple'" -->
		<div class="col-sm-9">
			<div class="d-flex flex-row gap-2">
				<template v-if="dataprops.search == 'simple'">
					<label class="col-form-label col-form-label-sm me-3">SEARCH</label>
					<input type="text" class="form-control form-control-sm searchBox" v-model="searchString" :disabled="showAdvanceFilter">
					<button class="btn btn-sm btn-dark px-3" type="button" @click="search(1)" :disabled="showAdvanceFilter"><i class="ph-magnifying-glass"></i></button>
					<button class="btn btn-sm" type="button" @click="chooseActiveRecords()" :class="activeOnly == 1 ? 'btn-success' : 'btn-dark'">Show<span v-if="activeOnly == 1">ing</span> Active</button>
					<button class="btn btn-sm btn-outline-dark" type="button" @click="search(-1)" v-if="searchString.length > 0 || activeOnly == 1">Show All</button>
				</template>
				<button class="btn btn-sm btn-dark text-uppercase" type="button" v-if="dataprops.search == 'advanced'" @click="showAdvanceFilter = !showAdvanceFilter"><span v-if="!showAdvanceFilter">Show</span><span v-if="showAdvanceFilter">Hide</span> SEARCH Filters</button>
				<label class="form-label text-success fw-bold p-1 m-0" v-if="activeOnly == 1">Showing Active records</label>
			</div>
		</div>
		<div class="col-sm-3 text-end">
			<div class="input-group d-inline-flex">
				<label class="col-form-label col-form-label-sm me-3">EXPORT</label>
				<select class="form-select form-select-sm" v-model="download">
					<option value="XLSX">Excel (XLSX)</option>
					<option value="XLS">Excel (XLS)</option>
					<option value="CSV">Comma Separated (CSV)</option>
					<option value="ODS">Open Document Format (ODS)</option>
					<!-- <option value="PDF">PDF</option> -->
				</select>
				<a href="#" class="btn btn-sm btn-dark" @click="exportData()">Export</a>
			</div>
		</div>
	</div>
	<template v-if="showAdvanceFilter">
		<!-- <h5 v-if="dataprops.search && dataprops.search == 'advanced'" class="mt-3">FILTERS</h5> -->
		<div class="card mb-3" v-if="dataprops.search && dataprops.search == 'advanced'">
			<div class="card-body py-2">
				<div class="row mb-1 d-flex align-items-center">
					<div class="col-3 fw-semibold py-2">
						ADD FILTER
					</div>
				</div>
				<div class="row mb-3 d-flex align-items-center">
					<div class="col-3">
						<select class="form-select" v-model="selectedColumn">
							<option :value="column" v-for="column of defaultColumns">{{ column.title }}</option>
							<optgroup label="COLUMNS">
								<option :value="column" v-for="column of dataprops.search_params?.columns">{{ column.title }}</option>
							</optgroup>
						</select>
					</div>
					<div class="col-3">
						<select class="form-select" v-model="searchCondition" v-if="selectedColumn">
							<!-- <option value="-1">ANY</option> -->
							<!-- Condition options for DD -->
							<template v-if="selectedColumn.type == 'dropdown' || selectedColumn.type == 'master'">
								<option value="0">IS NOT</option>
								<option value="1">IS</option>
							</template>
							<!-- Condition options for DD -->
							<!-- Condition options for Text -->
							<template v-if="selectedColumn.type == 'text'">
								<option value="0">WHERE TEXT IS NOT</option>
								<option value="1">MATCHING</option>
								<option value="22">CONTAINING</option>
							</template>
							<!-- Condition options for Text -->
							<!-- Condition options for Date -->
							<template v-if="selectedColumn.type == 'date'">
								<option value="0">IS NOT ON</option>
								<option value="1">ON</option>
								<option value="2">ON OR AFTER</option>
								<option value="3">ON OR BEFORE</option>
								<option value="5">BETWEEN</option>
							</template>
								<!-- Condition options for Date -->
							<!-- Condition options for Number -->
							<template v-if="selectedColumn.type == 'number'">
								<option value="0">DOES NOT EQUALS</option>
								<option value="1">EQUALS</option>
								<option value="12">LESS THAN OR EQUALS</option>
								<option value="13">GREATER THAN OR EQUALS</option>
								<option value="15">BETWEEN</option>
							</template>
							<!-- Condition options for Number -->
							<!-- Condition options for Boolean -->
							<template v-if="selectedColumn.type == 'boolean'">
								<option value="0">TRUE</option>
								<option value="1">FALSE</option>
							</template>
							<!-- Condition options for Boolean -->
						</select>
					</div>
					<div class="col-6">
						<template v-if="selectedColumn">
							<div class="row" v-if="selectedColumn.type == 'date'">
								<div class="col-sm-4">
									<input type="date" class="form-control" v-model="fromDate">
								</div>
								<label class="text-center col-form-label col-form-label-sm col-1" v-if="searchCondition == '5'">TO</label>
								<div class="col-sm-4" v-if="searchCondition == '5'">
									<input type="date" class="form-control" :min="fromDate" :max="selectedColumn.max" v-model="toDate">
								</div>
								<div class="col-2">
									<button type="button" class="btn btn-dark btn-sm" v-if="selectedColumn && searchCondition" @click="createFilter">ADD</button>
								</div>
							</div>
							<div class="row" v-else-if="selectedColumn.type == 'number'">
								<div class="col-sm-4">
									<input type="number" class="form-control" :min="selectedColumn.min" :max="selectedColumn.max" v-model="fromValue">
								</div>
								<label class="text-center col-form-label col-form-label-sm col-1" v-if="searchCondition == '15'">TO</label>
								<div class="col-sm-4" v-if="searchCondition == '15'">
									<input type="number" class="form-control" :min="fromValue" :max="selectedColumn.max" v-model="toValue">
								</div>
								<div class="col-2">
									<button type="button" class="btn btn-dark btn-sm" v-if="selectedColumn && searchCondition" @click="createFilter">ADD</button>
								</div>
							</div>
							<div class="row" v-else-if="selectedColumn.type == 'dropdown'">
								<div class="col-sm-4">
									<select class="form-select" v-model="searchValue" v-if="selectedColumn.source_enum && selectedColumn.source_enum.length > 0">
										<option :value="enumRow.id" v-for="enumRow of selectedColumn.source_enum">{{ enumRow.value }}</option>
									</select>
								</div>
								<div class="col-2">
									<button type="button" class="btn btn-dark btn-sm" v-if="selectedColumn && searchCondition" @click="createFilter">ADD</button>
								</div>
							</div>
							<div class="row" v-else-if="selectedColumn.type == 'master'">
								<div class="col-sm-4">
									<select class="form-select" v-model="searchValue" v-if="this.apiResponseData[selectedColumn.property]">
										<option :value="enumRow.id" v-for="enumRow of this.apiResponseData[selectedColumn.property]">{{ enumRow.value }}</option>
									</select>
								</div>
								<div class="col-2">
									<button type="button" class="btn btn-dark btn-sm" v-if="selectedColumn && searchCondition" @click="createFilter">ADD</button>
								</div>
							</div>
							<div class="row" v-else>
								<div class="col-sm-4">
									<input type="text" class="form-control" v-model="searchValue">
								</div>
								<div class="col-2">
									<button type="button" class="btn btn-dark btn-sm" v-if="selectedColumn && searchCondition" @click="createFilter">ADD</button>
								</div>
							</div>
						</template>
					</div>
				</div>
				<template v-if="filters && filters.length > 0">
					<div class="row mb-1 d-flex">
						<div class="col-5 fw-semibold py-1 border-bottom text-uppercase">Condition</div>
						<div class="col-1 fw-semibold py-1 border-bottom text-uppercase">Actions</div>
					</div>
					<div class="row mb-1 d-flex" v-for="singleFilter of filters">
						<div class="col-5 border-bottom">
							<div class="d-flex flex-row gap-1 py-2">
								<div>{{ singleFilter.title }}</div>
								<template v-if="singleFilter.type == 'date'"> 
									<span v-if="singleFilter.condition == 0">IS NOT ON</span>
									<span v-if="singleFilter.condition == 1">IS ON</span>
									<span v-if="singleFilter.condition == 2">ON OR BEFORE</span>
									<span v-if="singleFilter.condition == 3">ON OR AFTER</span>
									<span v-if="singleFilter.condition == 5">BETWEEN</span>
									<span>{{ singleFilter.from_value }}</span>
									<span v-if="singleFilter.condition == 5 || singleFilter.condition == 15">AND</span>
									<span v-if="singleFilter.condition == 5 || singleFilter.condition == 15">{{ singleFilter.to_value }}</span>
								</template>
								<template v-else-if="singleFilter.type == 'number'"> 
									<span v-if="singleFilter.condition == 0">NOT EQUALS</span>
									<span v-if="singleFilter.condition == 1">EQUALS</span>
									<span v-if="singleFilter.condition == 12">LESS THAN OR EQUALS</span>
									<span v-if="singleFilter.condition == 13">GREATER THAN OR EQUALS</span>
									<span v-if="singleFilter.condition == 15">BETWEEN</span>
									<span>{{ singleFilter.from_value }}</span>
									<span v-if="singleFilter.condition == 5 || singleFilter.condition == 15">AND</span>
									<span v-if="singleFilter.condition == 5 || singleFilter.condition == 15">{{ singleFilter.to_value }}</span>
								</template>
								<template v-else-if="singleFilter.type == 'dropdown'">
									<span v-if="singleFilter.condition == 0">IS NOT</span>
									<span v-if="singleFilter.condition == 1">IS</span>
									<template v-if="singleFilter.source_enum && singleFilter.source_enum.length > 0" v-for="sourceEnum of singleFilter.source_enum">
										<span v-if="sourceEnum.id == singleFilter.search_for_value">{{ sourceEnum.value }}</span>
									</template>
								</template>
								<template v-else-if="singleFilter.type == 'master'">
									<span v-if="singleFilter.condition == 0">IS NOT</span>
									<span v-if="singleFilter.condition == 1">IS</span>
									{{ apiResponseData[singleFilter.property] }}
								</template>
								<template v-else-if="singleFilter.type == 'text'"> 
									<span v-if="singleFilter.condition == 0">WHERE TEXT IS NOT</span>
									<span v-if="singleFilter.condition == 1"></span>
									<span v-if="singleFilter.condition == 22">CONTAINING</span>
									<span>"{{ singleFilter.search_for_value }}"</span>
								</template>
							</div>
						</div>
						<div class="col-1 border-bottom">
							<button type="button" class="btn btn-danger btn-sm" @click="removeFilter(singleFilter)">REMOVE</button>
						</div>
					</div>
				</template>
			</div>
			<div class="card-footer">
				<button type="button" class="btn btn-dark btn-sm me-3" @click="loadAllObjects" :disabled="filters.length == 0">APPLY FILTERS</button>
				<button type="button" class="btn btn-outline-dark btn-sm me-3" @click="resetFilters()" :disabled="filters.length == 0">RESET FILTERS</button>
				<!-- <button type="button" class="btn btn-outline-dark btn-sm" @click="showAdvanceFilter = !showAdvanceFilter">Cancel</button> -->
			</div>
		</div>
	</template>
	<div v-if="dataprops.mode && dataprops.mode == 'card'">
		<div class="card rounded-4 mb-1 border-0 bg-transparent">
			<div class="card-body py-2">
				<div class="row">
					<div class="col-md-1" v-if="dataprops.card_props?.include_logo_in_row">
						&nbsp;
					</div>
					<div v-for="column of dataprops.columns" 
						:key="column.id" 
						:class="'col-md-' + column.width + ' ' + (column.class ? column.class : '') + ' ' + (column.sortable == true ? 'sortable' : '') + ' ' + (column.align == 'center' ? 'text-center' : (column.align == 'right' ? 'text-end' : ''))" @click="sortByColumn(column)">
						<span>{{ column.title }}</span>
						<i class="pt-1 float-end ph-sort-ascending" v-if="currentColumnSortBy == column.property && currentSortOrder == 'asc'"></i>
						<i class="pt-1 float-end ph-sort-descending" v-if="currentColumnSortBy == column.property && currentSortOrder == 'desc'"></i>
					</div>
					<div class="col-md-3 text-center">
						ACTIONS
					</div>
				</div>
			</div>
		</div>
		<div v-if="allRows && allRows.length > 0">
			<div class="card mb-3 rounded-4" v-for="row of allRows" :key="row.id" :class="' ' + dataprops.card_props?.row_class">
				<div class="card-body">
					<div class="row">
						<div class="col-md-1" v-if="dataprops.card_props?.include_logo_in_row">
							{{ rowValue.value.substr(0, 1) }}
						</div>
						<div v-for="(rowValue, index) in row.rowValues" :key="rowValue.id" :class="rowValue.class + ' ' + 'col-md-' + dataprops.columns[index]?.width + ' ' + (dataprops.columns[index]?.class ? dataprops.columns[index]?.class : '') + ' ' + (dataprops.columns[index]?.sortable == true ? 'sortable' : '') + ' ' + (dataprops.columns[index]?.align == 'center' ? 'text-center' : (dataprops.columns[index]?.align == 'right' ? 'text-end' : ''))">
							<div v-if="dataprops.columns[index]?.date_type">
								<div v-if="dataprops.columns[index]?.date_type == 'mysqldate'">
									<span v-if="dataprops.columns[index]?.display_type == 'date' && dataprops.columns[index]?.format">{{ formatMySQLDate(rowValue.value, dataprops.columns[index]?.format) }}</span>
									<span v-else>{{ rowValue.value }}</span>
								</div>
								<div v-if="dataprops.columns[index]?.date_type == 'timestamp'">
									<span v-if="dataprops.columns[index]?.display_type == 'date' && dataprops.columns[index]?.format">{{ formatISODate(rowValue.value, dataprops.columns[index]?.format) }}</span>
									<span v-else>{{ rowValue.value }}</span>
								</div>
							</div>
							<div v-else>{{ rowValue.value }}</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div v-if="dataprops.mode && dataprops.mode == 'vcard'">
	</div>
	<div v-else>
		<table class="table table-striped table-sm {{dataprops.class}}" :id="dataprops.id">
			<thead class="table-dark">
				<tr><!--column.hasOwnProperty('align') && -->
					<th v-for="column of dataprops.columns" 
						:key="column.id" 
						:width="column.width"
						:class="(column.class ? column.class : '') + ' '+ (column.sortable == true ? 'sortable': '') + ' ' + (column.align == 'center' ? 'text-center':(column.align == 'right'?'text-end':''))" @click="sortByColumn(column)">
						<span>{{column.title}}</span>
						<i class="pt-1 float-end ph-sort-ascending" v-if="currentColumnSortBy == column.property && currentSortOrder == 'asc'"></i>
						<i class="pt-1 float-end ph-sort-descending" v-if="currentColumnSortBy == column.property && currentSortOrder == 'desc'"></i>
					</th>
					<th class="text-center">ACTIONS</th>
				</tr>
			</thead>
			<tbody>
				<template v-if="allRows && allRows.length > 0">
					<tr v-for="row of allRows" :key="row.id">
						<template v-for="(rowValue, index) in row.rowValues" :key="rowValue.id">
							<td :class="rowValue.class + ' ' + (dataprops.columns[index]?.align == 'center' ? 'text-center' : (dataprops.columns[index]?.align == 'right' ? 'text-end' : ''))">
								<div v-if="dataprops.columns[index]?.date_type">
									<div v-if="dataprops.columns[index]?.date_type == 'mysqldate'">
										<span v-if="dataprops.columns[index]?.display_type == 'date' && dataprops.columns[index]?.format">{{ formatMySQLDate(rowValue.value, dataprops.columns[index]?.format) }}</span>
										<span v-else>{{ rowValue.value }}</span>
									</div>
									<div v-if="dataprops.columns[index]?.date_type == 'timestamp'">
										<span v-if="dataprops.columns[index]?.display_type == 'date' && dataprops.columns[index]?.format">{{ formatISODate(rowValue.value, dataprops.columns[index]?.format) }}</span>
										<span v-else>{{ rowValue.value }}</span>
									</div>
								</div>
								<div v-else>{{rowValue.prefix }}{{ rowValue.value }}{{ rowValue.suffix }}</div>
							</td>
						</template>
						<td class="text-center">
							<span class="badge rounded-pill bg-success" v-if="row.status == 1">ACTIVE</span>
							<span class="badge rounded-pill bg-danger" v-if="row.status == 0">INACTIVE</span>
							<span class="badge rounded-pill bg-secondary" v-if="row.status == -1">CANCELLED</span>
						</td>
						<td class="text-center">
							<div class="d-flex flex-row gap-2 justify-content-center">
								<a class="btn btn-sm" :class="action.class" href="#" role="button" v-for="(action, key) of row.rowObject.actions" @click="$emit(action.action, row.rowObject, action.additional_params?.join(','))">{{ action.title }}</a>
							</div>
						</td>
					</tr>
				</template>
				<tr v-else>
					<td class="text-center" :colspan="dataprops.columns.length+1">
						No Objects found
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="row">
		<div class="col-sm-1 justify-content-end d-flex align-items-center pe-0" v-if="totalPages > 1">
			<label class="form-label m-0">Page No:</label>
		</div>
		<div class="col-sm-1" v-if="totalPages > 1">
			<select class="form-select form-select-sm" v-model="currentPage">
				<option :value="index" v-for="index in totalPages" :key="index">{{index}}</option>
			</select>
		</div>
		<div class="col-sm-6 d-flex align-items-center gap-1">
			Showing <span v-if="currentFrom < currentTo">{{ currentFrom }} to {{ currentTo }}</span><span v-if="currentFrom == currentTo">{{ currentFrom }}</span> of {{ totalRecords }} entries.
		</div>
	</div>
	<form ref="exportForm" method="post" :action="dataprops.base_url + 'get'">
		<input type="hidden" name="download" :value="download">
		<input type="hidden" name="q" :value="searchString">
		<input type="hidden" name="active" :value="(activeOnly == 1?1:-1)">
		<input type="hidden" name="sortBy" :value="currentColumnSortBy">
		<input type="hidden" name="sortOrder" :value="currentSortOrder">
	</form>
</template>

<script>
    export default{
        props: [ 'dataprops' ],
		// emits: ['viewObject', 'editObject', 'toggleObjectStatus'],
		data(){
			return{
				allRows: [],
				currentColumnSortBy: "",
				currentSortOrder: 'asc',
				// This is for filters
				defaultColumns: [
					{ title: "SEARCH FOR TEXT", property: "__q", type: "text" },
				],
				selectedColumn: null,
				searchCondition: null,
				searchValue: "",
				filters: [],
				apiResponseData: [],
				// This is for filters
				download: "",
				currentPage: 1,
				searchString: "",
				activeOnly: -1,
				showAdvanceFilter: false,
				currentPage: 1,
				currentFrom: 1,
				currentTo: 10,
				totalRecords: 0,
				totalPages: 0
			}
		},
		methods: {
			search(type){
				this.currentPage = 1;
				if( type == 1 ){
					if( this.searchString == "" ){
						Swal.fire({
							icon: "error",
							text: "Search string cannot be empty"
						});
					}
				}
				else {
					this.activeOnly = -1;
					this.searchString = "";
					this.resetFilters();
				}
				this.loadAllObjects();
			},
			chooseActiveRecords() {
				if( this.activeOnly != 1 ){
					this.activeOnly = 1;	
					this.loadAllObjects();
				}
			},
			createFilter() {
				let _selectedColumn = Object.assign({}, this.selectedColumn);
				let used = false;
				// Check
				let objFound = this.filters.find((singleFilter) => {
					return (singleFilter.title == _selectedColumn.title); 
				});
				if (objFound) {
					this.showToast("You cannot add the same column to the filter again", "error", "bottom", 3000);
					return
				}
				let filterToPush = {
					title: _selectedColumn.title,
					property: _selectedColumn.property,
					type: _selectedColumn.type,
					condition: this.searchCondition,
					source_enum: (_selectedColumn.source_enum ? _selectedColumn.source_enum : null)
				};
				if (_selectedColumn.type == 'date') {
					filterToPush.from_value = this.fromDate;
					filterToPush.to_value = this.toDate;
				}
				else if (_selectedColumn.type == 'number') {
					filterToPush.from_value = this.fromValue;
					filterToPush.to_value = this.toValue;
				}
				else {
					filterToPush.search_for_value = this.searchValue;
				}
				this.filters.push(filterToPush);
				this.selectedColumn = null;
				this.searchCondition = null;
				this.fromDate = null;
				this.toDate = null;
				this.fromValue = null;
				this.toValue = null;
				console.log(this.apiResponseData);
			},
			removeFilter(singleFilter) {
				this.filters.splice(this.filters.indexOf(singleFilter), 1);
				this.showToast("Filter has been removed.", "info", "bottom", 2000);
				this.loadAllObjects();
			},
			resetFilters() {
				this.filters = [];
				this.selectedColumn = null;
				this.searchCondition = null;
				this.fromDate = null;
				this.toDate = null;
				this.fromValue = null;
				this.toValue = null;
				this.searchValue = null;
				this.loadAllObjects();
			},
			loadAllObjects(){
				if(this.dataprops && this.dataprops.base_url && this.dataprops.base_url.length > 0){
					var thisElem = this;
					var URL = this.dataprops.base_url+'get';
					var postArr = {
						page: this.currentPage
					};
					postArr['search'] = this.dataprops.search;
					// if (this.dataprops.search == 'advanced'){
						// check for advanced filters
						if (this.filters.length > 0) {
							postArr["advfilters"] = this.filters;
						}
					// }
					// else {
						if (this.searchString !== "")
							postArr["q"] = this.searchString;
					// }
					if( this.activeOnly == 1 )
						postArr["active"] = "1";
					if ( this.currentColumnSortBy.length > 0) {
						postArr["sortBy"] = this.currentColumnSortBy;
						postArr["sortOrder"] = this.currentSortOrder;
					}
					this.showLoading("Loading ...");
					axios.post(URL, postArr)
						.then(function (response) {
							thisElem.totalPages = response.data.last_page;
							thisElem.totalRecords = response.data.total;
							thisElem.currentFrom = response.data.from;
							thisElem.currentTo = response.data.to;
							thisElem.allRows = response.data.data.map((row) => {
								let objToReturn = {
									id: row.id,
									rowValues: [],
									rowObject: row,
									status: -1
								};
								let id = 1;
								let rowValues = [], allColumns = [];
								thisElem.dataprops.columns.forEach((column) => {
									// console.log(column);
									// if (column.hasOwnProperty('column_type') && column.column_type == 'joint'
									// 	&& column.hasOwnProperty('properties') && column.properties.length > 0) {
									// 	column.properties.forEach((property) => {
									// 		allColumns.push(property);
									// 	})
									// }
									// else{
										allColumns.push(column);
									// }
								});
								thisElem.dataprops.columns.forEach( (column) => {
									if (column.type > 0) {
										if (column.hasOwnProperty('column_type') && column.column_type == 'joint'
											&& column.hasOwnProperty('properties') && column.properties.length > 0) {
											let objValue = null;	
											let objData = "";
											column.properties.forEach((property) => {
												// console.log(">>>> Obj2 ", property, objValue);
												let propertyValue = thisElem.processColumnForDisplay(row, property);
												if (!objValue)
													objValue = Object.assign({}, propertyValue);
												objData += propertyValue.value + " ";
											})
											objValue.value = objData.trim();
											rowValues.push(objValue);
										}
										else {
											let objValue = thisElem.processColumnForDisplay(row, column);
											rowValues.push(objValue);
										}
									}
								});
								objToReturn.rowValues = rowValues;
								objToReturn.status = row.status;
								return objToReturn;
							});
							thisElem.totalPages = response.data.last_page;
							thisElem.totalRecords = response.data.total;
							thisElem.closeSwal();
						})
						.catch(function (error) {
							thisElem.closeSwal();
							console.log(error);
						});
				}
			},
			processColumnForDisplay(row, column) {
				let value = "";
				if (column["property"].indexOf(".") > 0) {
					let columnParts = column["property"].split(".");
					let objectToDig = null;
					columnParts.forEach((columnPart) => {
						if (objectToDig == null) {
							objectToDig = row[columnPart];
							if (!objectToDig)
								objectToDig = "Not Found";
						}
						else {
							if (objectToDig[columnPart])
								objectToDig = objectToDig[columnPart];
							else {
								if (column["alt_value"] && column["alt_value"].length > 0)
									objectToDig = column["alt_value"];
								else
									objectToDig = "Not Found";
							}
						}
					});
					value = objectToDig;
				}
				else {
					value = row[column["property"]];
					// console.log("&&&&&& print ", row, column["property"], value);
				}
				if (column.hasOwnProperty("enum") && column["enum"]) {
					let enumProp = column["enum"];
					if (enumProp.hasOwnProperty(value))
						value = enumProp[value];
				}
				let objValue = {
					id: 0, value: (value == null ? "-" : value), type: 1, class: ""
				}
				if (column.hasOwnProperty("class") && column["class"].length > 0)
					objValue.class = column["class"];
				if (column.hasOwnProperty("prefix"))
					objValue.prefix = column["prefix"];
				if (column.hasOwnProperty("suffix"))
					objValue.suffix = column["suffix"];
				return objValue;
			},
			sortByColumn(column){
				if( column.hasOwnProperty("sortable") && column.sortable == true ){
					if( this.currentColumnSortBy != -1 ){
						this.currentSortOrder = ( this.currentSortOrder == 'asc' ? 'desc':'asc' );
						if( this.currentColumnSortBy != column.property )
							this.currentSortOrder = 'asc';
					}
					this.currentColumnSortBy = column.property;
					this.loadAllObjects();
				}
			},
			exportData() {
				this.$refs.exportForm.submit();
			},
			// GET APIs
			async getAPIData(api) {
				let response = await axios.post(api.api, []);
				if (response.hasOwnProperty("data")) {
					this.apiResponseData["" + api.property] = [];
					for (let record of response.data) {
						let id = record["" + api.id];
						let value = record["" + api.value];
						this.apiResponseData["" + api.property].push({id: id, value: value});
					}
				}
			}
		},
        mounted: function() {
			this.loadAllObjects();
        },
        created: function() {
			let id = 0;
			this.dataprops.columns = this.dataprops.columns.map((column) => {
				column.id = id++;
				column.type = 1;
				return column;
			});
			// Search for apis
			let apis = [];
			if (this.dataprops.search == "advanced"
				&& this.dataprops.hasOwnProperty("search_params")
				&& this.dataprops.search_params.hasOwnProperty("columns")
				&& this.dataprops.search_params.columns.length > 0 ) {
				for (let column of this.dataprops.search_params.columns) {
					if (column.type == "master" && column.hasOwnProperty("source")
					&& column.source.hasOwnProperty("api") && column.source.api.length > 0
						&& column.source.hasOwnProperty("id") && column.source.hasOwnProperty("value")) {
						let columnSource = Object.assign({}, column.source);
						columnSource.property = column.property;
						apis.push(columnSource);
					}
				}
			}
			for (let api of apis) {
				this.getAPIData(api);
			}
			let statusAttribute = { title: 'STATUS', property: 'status', class: "text-center", type: 0, width: '10%' };
			if (this.dataprops.hasOwnProperty("mode") && this.dataprops.mode == "card") {
				statusAttribute.width = "2";
			}
			else
				statusAttribute.width = "10%";
			this.dataprops.columns.push(statusAttribute);
        },
		watch:{
			'dataprops.reload': {
				handler(newValue, oldValue) {
					this.dataprops.reload = false;
					this.loadAllObjects();
				}
			},
			currentPage: {
				handler(newValue, oldValue) {
					this.loadAllObjects();
				}
			}
		}
    }
</script>
<style>
.sortable{
	cursor: pointer;
}
.searchBox{
	width: 150px !important;
}
</style>
