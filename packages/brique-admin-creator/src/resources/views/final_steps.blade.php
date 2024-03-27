<!-- <div class="row mb-3">
	<div class="col-3 py-2 text-end">
		<label for="objectName" class="form-label">Show count of objects in menu</label>
	</div>
	<div class="col-4 py-2">
		<div class="d-flex flex-row gap-3">
			<div class="form-check">
				<input class="form-check-input" type="radio" name="count_objects" :id="'count1'+tbl" value="1" v-model="navigation_badge">
				<label class="form-check-label" :for="'count1'+tbl">Yes</label>
			</div>
			<div class="form-check">
				<input class="form-check-input" type="radio" name="count_objects" :id="'count2'+tbl" value="2" v-model="navigation_badge">
				<label class="form-check-label" :for="'count2'+tbl">No</label>
			</div>
		</div>
	</div>
</div> -->
<a href="#" title="" v-if="enableLinks" @click="downloadSomething('model')">Download Model</a>
<div class="row mb-3">
	<div class="col-12">
		<button type="button" class="btn btn-primary me-3" @click="getResourceCode()">Generate</button>
		<button type="button" class="btn btn-success" @click="saveResource()">Save</button>
	</div>
</div>