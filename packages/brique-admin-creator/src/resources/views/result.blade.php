<div class="row">
	<div class="col-6 offset-3">
		<div class="card mb-3">
			<div class="card-header">
				<h6 class="d-inline-block">Create the resource using the following statement.</h6>
				<a href="#" class="float-end" @click="copyCommand()">Copy</a>
			</div>
			<div class="card-body bg-light">
				<label class="form-label mb-2">Click Copy to create a Filament Resource</label>
				<input type="text" class="form-control" :value="'php artisan make:filament-resource '+gen_resource_name" readonly>
			</div>
		</div>
		<!-- Imports -->
		<div class="card mb-3">
			<div class="card-header">
				<h6 class="d-inline-block">Check if these imports are used.</h6>
			</div>
			<div class="card-body bg-light">
				<p class="m-0" v-for="importStmt of checkImports">(( importStmt ))</p>
			</div>
		</div>
		<!-- Imports -->
		<!-- Step 1 - ->
		<div class="card mb-3">
			<div class="card-header">
				<h6 class="d-inline-block">To handle grid and sections in the form, use the following imports.</h6>
				<div class="d-flex flex-row gap-2 float-end">
					<a href="#" class="float-end">Show Help</a>
					<a href="#" class="float-end">Copy</a>
				</div>
			</div>
			<div class="card-body bg-light">
				<p class="m-0" v-for="importStmt of newImports">(( importStmt ))</p>
			</div>
		</div>
		<!- - Step 1 -->
		<!-- Pages Method -->
		<div class="card mb-3" v-if="add_edit_mode == 2">
			<div class="card-header">
				<h6 class="d-inline-block">PAGES Method</h6>
			</div>
			<div class="card-body bg-light">
				Within the function `getPages()`, remove the 'create' and 'edit' array indices and their corresponding values.
			</div>
		</div>
		<!-- Pages Method -->
		<!-- Step 1 -->
		<div class="card mb-3" v-if="navigation_group && navigation_group.length > 0">
			<div class="card-header">
				<h6 class="d-inline-block">COPY and PASTE in Class.</h6>
				<a href="#" class="float-end" @click="copyClassProperties()">Copy</a>
			</div>
			<div class="card-body bg-light" ref="classProperties">
				protected static ?string $navigationGroup = '(( navigation_group ))';<br/>
				protected static ?string $modelLabel = '(( gen_object_label ))';
			</div>
		</div>
		<!-- Form Method -->
		<!--  v-html="nl2br(gen_form_content, true)" -->
		<div class="card mb-3">
			<div class="card-header">
				<h6 class="d-inline-block">COPY Content</h6>
			</div>
			<div class="card-body bg-light">
				<p>Copy the code below to paste in Resource file.</p>
				<div class="d-flex flex-row mb-3 gap-3">
					<button type="button" class="btn btn-primary" @click="copyFormContent()">Copy FORM METHOD</button>
					<button type="button" class="btn btn-info" @click="copyTableContent()">Copy TABLE METHOD</button>
					<button type="button" class="btn btn-success" @click="copyMenuItem()">Copy MENU ITEM</button>
				</div>
				<p v-if="uses_multiple_uploads">Copy the code below to paste in Model file.</p>
				<div class="d-flex flex-row gap-3" v-if="uses_multiple_uploads">
					<button type="button" class="btn btn-warning" @click="copyModelArray()">Copy MODEL ARRAY</button>
				</div>
			</div>
		</div>
		<!-- Form Method -->
	</div>
</div>