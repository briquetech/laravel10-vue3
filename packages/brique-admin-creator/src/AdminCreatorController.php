<?php
namespace BriqueAdminCreator;

use App\Models\GeneratedModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminCreatorController extends \App\Http\Controllers\Controller{

	//
	public function index(){
		// $databases = DB::connection()->select('SHOW DATABASES');
		// $databaseNames = array_column($databases, 'Database');
		// config(['database.connections.mysql.database' => env("DB_DATABASE")]);
		$tables = DB::select('SHOW TABLES');
		$tableNames = [];
		foreach ($tables as $table) {
			$tableValues = array_values(json_decode(json_encode($table), true));
			array_push($tableNames, $tableValues[0]);
		}
		return view('brique-admin-creator::creatorhome', compact("tableNames"));
	}

	// Not used anymore
	public function getTables(Request $request){
		$input = $request->all();
		$response = [];
		if( isset($input["db"]) ){
			config(['database.connections.mysql.database' => $input["db"]]);
			$tables = DB::select('SHOW TABLES');
			$tableNames = [];
			foreach ($tables as $table) {
				$tableValues = array_values(json_decode(json_encode($table), true));
				array_push($tableNames, $tableValues[0]);
			}
			// Extract table names from the result
			$response["tables"] = $tableNames;
			$response["status"] = 1;
		}
		else
			$response["status"] = -1;
		return response()->json($response);
	}

	public function getTableStructure(Request $request){
		$input = $request->all();
		$response = [];
		if( isset($input["tbl"]) ){
			// config(['database.connections.mysql.database' => $input["db"]]);
			$tableColumns = Schema::getColumnListing($input["tbl"]);
			$response["columns"] = $tableColumns;
			$response["status"] = 1;
		}
		else
			$response["status"] = -1;
		return response()->json($response);
	}

	public function downloadSomething($object, $code){
		$generatedModule = GeneratedModule::where('code', $code)->first();
		if( $object == "model" ){
			$jsonObject = json_decode($generatedModule["object"], true);

			$zip = new \ZipStream\ZipStream(
				outputName: $jsonObject["object_name"].'.zip',
				// enable output of HTTP headers
				sendHttpHeaders: true,
			);

			$columnNames = [];
			$fillableColumns = [];
			$formColumns = [];
			$searchColumns = [];
			$sortColumns = [];
			$requiredColumns = [];
			$tableColumns = [];
			$viewColumns = [];
			$formRelationshipColumns = [];
			$tableRelationshipColumns = [];
			$uniqueColumn = null;
			foreach($jsonObject['columns'] as $column){
				array_push($columnNames, $column["name"]);
				// Use in Form
				if( $column["use_in_form"] == true ){
					array_push($fillableColumns, $column["name"]);
					array_push($formColumns, $column);
				}
				// simple search
				if( $column["common_searchable"] == true )
					array_push($searchColumns, $column["name"]);
				// sort
				if( $column["sortable"] == true )
					array_push($sortColumns, $column["name"]);
				// required
				if( $column["required"] == true )
					array_push($requiredColumns, $column);
				// included in table 
				if( $column["use_in_table_view"] == true && $column["use_in_table"] == true )
					array_push($tableColumns, $column);
				// included in view
				if( $column["use_in_table_view"] == true && $column["use_in_view"] == true )
					array_push($viewColumns, $column);
				// form relationships
				if( $column["form_type"] == "select" ){
					if( !array_key_exists($column["name"], $formRelationshipColumns) )
						array_push($formRelationshipColumns, $column);
				}
				// table relationships
				if( $column["table_type"] == "relation" )
					array_push($tableRelationshipColumns, $column);
				// Check if unique
				if( $jsonObject["unique_column"] != null && $jsonObject["unique_column"] == $column["name"] )
					$uniqueColumn = $column;
			}
			// MODEL - replace tokens
			$modelContents = $this->generateModelContent($jsonObject, $fillableColumns, $tableRelationshipColumns);
			// =====================
			$controllerContents = $this->generateControllerContent($jsonObject, $searchColumns, $requiredColumns, $uniqueColumn, $tableRelationshipColumns);
			// =====================
			$componentContents = $this->generateComponentsContent($jsonObject, $formColumns, $tableColumns, $viewColumns);
			$componentContents = str_replace('{{uniqueColumn}}', $uniqueColumn["name"], $componentContents);
			$componentContents = str_replace('{{uniqueColumnLabel}}', $uniqueColumn["frm_label"], $componentContents);
			// =====================
			$exportContents = $this->generateExportContent($jsonObject, $requiredColumns);
			// =====================
			$readmeContentsArray = $this->generateReadmeContent($jsonObject, $formRelationshipColumns);
			$readmeContents = $readmeContentsArray["readme"];
			$componentMixinContents = $readmeContentsArray["mixin"];
			// =====================

			// =====================
			// Model
			$zip->addFile(
				fileName: 'app/Models/'.$jsonObject["object_name"].'.php',
				data: $modelContents,
			);
			// Controller
			$zip->addFile(
				fileName: 'app/Http/Controllers/'.$jsonObject["object_name"].'Controller.php',
				data: $controllerContents,
			);
			// Export
			$zip->addFile(
				fileName: 'app/Exports/'.$jsonObject["object_name"].'Export.php',
				data: $exportContents,
			);
			if( count($formRelationshipColumns) > 0 ){
				// Mixins
				$zip->addFile(
					fileName: 'resources/js/mixins/masters.js',
					data: $componentMixinContents,
				);
			}
			// Components
			$zip->addFile(
				fileName: 'resources/js/components/'.$jsonObject["object_name"].'Component.vue',
				data: $componentContents,
			);
			// README
			$zip->addFile(
				fileName: 'README',
				data: $readmeContents,
			);
			// Close the ZIP stream
			$zip->finish();
		}
	}

	public function generateModelContent($jsonObject, $fillableColumns, $relationshipColumns){
		// Create relationships
		$relationships = "";
		$modelRelationshipsContents = Storage::disk('local')->get('Model_RelationshipFunction.txt');
		foreach ($relationshipColumns as $column) {
			//related_to_model, related_to_model_id
			if( isset($column["tbl_relation_method"]) && $column["tbl_relation_method"] != null && strlen($column["tbl_relation_method"]) > 0 
				&& isset($column["related_to_model"]) && $column["related_to_model"] != null && strlen($column["related_to_model"]) > 0
				&& isset($column["related_to_model_id"]) && $column["related_to_model_id"] != null && strlen($column["related_to_model_id"]) > 0 ){
				$relationships .= $modelRelationshipsContents."\n\n";
				$relationships = str_replace('{{objectName}}', $jsonObject["object_name"], $relationships);
				$relationships = str_replace('{{tbl_relation_method}}', $column["tbl_relation_method"], $relationships);
				$relationships = str_replace('{{related_to_model}}', $column["related_to_model"], $relationships);
				$relationships = str_replace('{{this_model_id}}', $column["name"], $relationships);
				$relationships = str_replace('{{related_to_model_id}}', $column["related_to_model_id"], $relationships);
			}
		}
		$modelContents = Storage::disk('local')->get('Model.txt');
		$modelContents = str_replace('{{objectName}}', $jsonObject["object_name"], $modelContents);
		$modelContents = str_replace('{{table_name}}', $jsonObject["tbl"], $modelContents);
		// Append status column
		array_push($fillableColumns, $jsonObject["activate_deactivate_column"]);
		$modelContents = str_replace('{{all_columns}}', "'".implode("', '", $fillableColumns)."'", $modelContents);
		$modelContents = str_replace('{{relationships}}', $relationships, $modelContents);
		return $modelContents;
	}

	public function generateExportContent($jsonObject, $requiredColumns){
		$exportContents = Storage::disk('local')->get('Export.txt');
		$exportContents = str_replace('{{objectName}}', $jsonObject["object_name"], $exportContents);
		$exportColumnLabels = "";
		$exportMapColumns = "";
		foreach ($requiredColumns as $column) {
			$exportColumnLabels .= "'".$column['tbl_label']."',";
			$exportMapColumns .= "\$row->".$column['name'].",";
		}
		$exportContents = str_replace('{{exportColumnLabels}}', $exportColumnLabels, $exportContents);
		$exportContents = str_replace('{{exportMapColumns}}', $exportMapColumns, $exportContents);
		return $exportContents;
	}

	public function generateControllerContent($jsonObject, $searchColumns, $requiredColumns, $uniqueColumn, $tableRelationshipColumns){
		$controllerContents = Storage::disk('local')->get('Controller.txt');
		// Controller - replace tokens
		$controllerContents = str_replace('{{objectName}}', $jsonObject["object_name"], $controllerContents);
		$controllerContents = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $controllerContents);
		$simpleSearchColumns = "";
		$advancedSearchColumnsMatch = "";
		$advancedSearchColumnsLike = "";
		$i = 0;
		foreach ($searchColumns as $column) {
			if( $i++ == 0){
				$simpleSearchColumns = "where('".$column."', 'like', '%'.trim(\$input['q']).'%')";
				$advancedSearchColumnsMatch = "where('".$column."', trim(\$filter['search_for_value']))";
				$advancedSearchColumnsLike = "where('".$column."', 'like', '%'.trim(\$filter['search_for_value']).'%')";
			}
			else{
				$simpleSearchColumns .= "->orWhere('".$column."', 'like', '%'.trim(\$input['q']).'%')"; 
				$advancedSearchColumnsMatch .= "->orWhere('".$column."', trim(\$filter['search_for_value']))";
				$advancedSearchColumnsLike .= "->orWhere('".$column."', 'like', '%'.trim(\$filter['search_for_value']).'%')";
			}
			if( $i < count($searchColumns)-1 ){
				$simpleSearchColumns .= "\n";
				$advancedSearchColumnsMatch .= "\n";
				$advancedSearchColumnsLike .= "\n";
			}
		}
		$requiredColumnsCondition = "";
		foreach ($requiredColumns as $column) {
			$requiredColumnsCondition .= "'".$column['name']."' => 'required";
			if( $column["form_type"] == "text" || $column["form_type"] == "textarea" || $column["form_type"] == "richeditor" || $column["form_type"] == "password" || $column["form_type"] == "radio" || $column["form_type"] == "yesno" || $column["form_type"] == "checkbox" )
				$requiredColumnsCondition .= "|string";
			elseif( $column["form_type"] == "email" )
				$requiredColumnsCondition .= "|email:filter";
			elseif( $column["form_type"] == "numeric" )
				$requiredColumnsCondition .= "|integer";
			elseif( $column["form_type"] == "decimal" )
				$requiredColumnsCondition .= "|decimal:2";
			elseif( $column["form_type"] == "telephone" )
				$requiredColumnsCondition .= "|min:6|max:12";
			elseif( $column["form_type"] == "url" )
				$requiredColumnsCondition .= '|url:http,https';
			elseif( $column["form_type"] == "date" )
				$requiredColumnsCondition .= '|date';
			elseif( $column["form_type"] == "datetime" )
				$requiredColumnsCondition .= '|datetime';
			elseif( $column["form_type"] == "toggle" )
				$requiredColumnsCondition .= '|boolean';
			elseif( $column["form_type"] == "fileupload" )
				$requiredColumnsCondition .= '|file';
			$requiredColumnsCondition .= "',\n";
		}
		// log::info($simpleSearchColumns);
		$controllerContents = str_replace('{{simpleSearchColumns}}', $simpleSearchColumns, $controllerContents);
		$controllerContents = str_replace('{{advancedSearchColumns-match}}', $advancedSearchColumnsMatch, $controllerContents);
		$controllerContents = str_replace('{{advancedSearchColumns-like}}', $advancedSearchColumnsLike, $controllerContents);
		$controllerContents = str_replace('{{addRequiredColumns}}', $requiredColumnsCondition, $controllerContents);
		$uniqueColumnContents = "";
		if( $uniqueColumn != null ){
			$uniqueColumnContents = Storage::disk('local')->get('ControllerUniqueCheck.txt');
			$uniqueColumnContents = str_replace('{{objectName}}', $jsonObject["object_name"], $uniqueColumnContents);
			$uniqueColumnContents = str_replace('{{uniqueColumn}}', $uniqueColumn["name"], $uniqueColumnContents);
			$uniqueColumnContents = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $uniqueColumnContents);
			$uniqueColumnContents = str_replace('{{uniqueColumnLabel}}', $uniqueColumn["frm_label"], $uniqueColumnContents);
		}
		$controllerContents = str_replace('{{uniqueColumnPart}}', $uniqueColumnContents, $controllerContents);
		$withClause = "";
		if( count($tableRelationshipColumns) > 0 ){
			$withClause = "with(";
			foreach ($tableRelationshipColumns as $tableRelationshipColumn) {
				$withClause .= "'".$tableRelationshipColumn["tbl_relation_method"]."', ";
			}
			$withClause = substr($withClause, 0, strlen($withClause)-2);
			$withClause .= ")->";

		}
		$controllerContents = str_replace('{{withClause}}', $withClause, $controllerContents);
		return $controllerContents;
	}
	
	public function generateComponentsContent($jsonObject, $formColumns, $tableColumns, $viewColumns){
		$componentContents = "";
		if( $jsonObject["add_edit_mode"] == 1 ){
		}
		else if( $jsonObject["add_edit_mode"] == 2 ){
			// popup mode
			$componentContents = Storage::disk('local')->get('ComponentPopup.txt');
			$componentAddEditFormContents = $this->generatePopupComponentAddEditContent($jsonObject, $formColumns);
			$componentViewRecordContents = $this->generatePopupComponentViewContent($jsonObject, $viewColumns);
			// =========
			$componentContents = str_replace('{{addEditModal}}', $componentAddEditFormContents, $componentContents);
			$componentContents = str_replace('{{viewRecordModal}}', $componentViewRecordContents, $componentContents);
			$componentContents = str_replace('{{objectName}}', $jsonObject["object_name"], $componentContents);
			$componentContents = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $componentContents);
			$componentContents = str_replace('{{objectName-uppercase}}', strtoupper($jsonObject["object_name"]), $componentContents);
			$componentContents = str_replace('{{objectLabel}}', $jsonObject["object_label"], $componentContents);
			$componentContents = str_replace('{{objectLabel-uppercase}}', strtoupper($jsonObject["object_label"]), $componentContents);
			$vueAddEditFieldsDefine = "";
			$vueAddEditFieldsValidation = "";
			$formRelationshipColumns = [];
			$counter = 0;
			foreach($formColumns as $formColumn){
				$vueAddEditFieldsDefine .= $formColumn["name"].":";
				$validationClause = "{ ";
				if( $formColumn["required"] )
					$validationClause .= " required, ";
				if( $formColumn["form_type"] == "email" ){
					$validationClause .= "email";
					$vueAddEditFieldsDefine .= "''";
				}
				else if( $formColumn["form_type"] == "numeric" ){
					$validationClause .= "numeric, minValueValue: minValue(0)";
					$vueAddEditFieldsDefine .= "0";
				}
				else if( $formColumn["form_type"] == "decimal" ){
					$validationClause .= "numeric, minValueValue: minValue(0)";
					$vueAddEditFieldsDefine .= "0";
				}
				else if( $formColumn["form_type"] == "numeric" ){
					$validationClause .= "numeric";
					$vueAddEditFieldsDefine .= "0";
				}
				else if( $formColumn["form_type"] == "url" ){
					$validationClause .= "url";
					$vueAddEditFieldsDefine .= "''";
				}
				else if( $formColumn["form_type"] == "date" || $formColumn["form_type"] == "datetime" ){
					$validationClause .= "";
					$vueAddEditFieldsDefine .= "''";
				}
				else if( $formColumn["form_type"] == "select"  ){
					$vueAddEditFieldsDefine .= "null";
					if( array_search($formColumn["frm_select_type"], ["relation", "fixed", "popup"]) !== FALSE )
						array_push($formRelationshipColumns, $formColumn);
				}
				else if( $formColumn["form_type"] == "radio" || $formColumn["form_type"] == "yesno" || $formColumn["form_type"] == "checkbox" ){
					$vueAddEditFieldsDefine .= "false";
				}
				else{
					$validationClause .= "minLengthValue: minLength(1)";
					$vueAddEditFieldsDefine .= "''";
				}
				$validationClause .= " }";
				$vueAddEditFieldsDefine .= ",".($counter < count($formColumns)-1 ? "\n":"");
				$vueAddEditFieldsValidation .= $formColumn["name"] . ": " . $validationClause . ",\n";
			}
			$componentContents = str_replace('{{vue-addEditFields}}', $vueAddEditFieldsDefine, $componentContents);
			$componentContents = str_replace('{{vue-addEditFieldValidations}}', $vueAddEditFieldsValidation, $componentContents);
			$componentContents = str_replace('{{searchType}}', $jsonObject["search_type"], $componentContents);
			// ======= Relationships
			// masterVariables, loadMasters, reloadMasters
			$masterVariables = "";
			$loadMasters = "";
			$reloadMasters = "";
			foreach($formRelationshipColumns as $formRelationshipColumn){
				$masterVariables .= "all".$this->getPascalCase($formRelationshipColumn["name"])."List: [],\n";
				if( $formRelationshipColumn["frm_select_type"] == "relation" ){
					$loadMasters .= "this.all".$this->getPascalCase($formRelationshipColumn["name"])."List"." = await this.loadAll".$formRelationshipColumn["frm_related_model"]."(true);\n";
					$reloadMasters .= "thisVar.all".$this->getPascalCase($formRelationshipColumn["name"])."List"." = await thisVar.loadAll".$formRelationshipColumn["frm_related_model"]."(true);\n";
				}
				else if( $formRelationshipColumn["frm_select_type"] == "fixed" ){
					$loadMasters .= "this.all".$this->getPascalCase($formRelationshipColumn["name"])."List"." = [";
					if( is_array($formRelationshipColumn["frm_options"]) && count($formRelationshipColumn["frm_options"]) > 0 ){
						foreach ($formRelationshipColumn["frm_options"] as $formColumn) {
							$loadMasters .= "{ id: '".$formColumn["key"]."', title: '".$formColumn["value"]."'}, ";
						}
					}
					$loadMasters .= "];\n";
				}
			}
			$componentContents = str_replace('{{masterVariables}}', $masterVariables, $componentContents);
			$componentContents = str_replace('{{loadMasters}}', $loadMasters, $componentContents);
			$componentContents = str_replace('{{reloadMasters}}', $reloadMasters, $componentContents);
			$componentContents = str_replace('{{uniqueColumn}}', $jsonObject["unique_column"], $componentContents);

			// =======
			$tableContent = $this->generateTableContent($jsonObject, $tableColumns, $jsonObject["search_type"]);
			$componentContents = str_replace('{{listFields}}', $tableContent["fieldsList"], $componentContents);
			$componentContents = str_replace('{{advancedSearchParams}}', $tableContent["advancedSearchContent"], $componentContents);
		}
		return $componentContents;
	}

	public function generatePopupComponentAddEditContent($jsonObject, $fillableColumns){
		$componentAddEditFormContents = Storage::disk('local')->get('ComponentAddEdit.txt');
		$componentAddEditComponent = Storage::disk('local')->get('ComponentAddEditComponent.txt');
		$componentInputFormField = Storage::disk('local')->get('ComponentInputFormField.txt');
		$componentFileFormField = Storage::disk('local')->get('ComponentFileFormField.txt');
		$componentTextAreaFormField = Storage::disk('local')->get('ComponentTextAreaFormField.txt');
		$componentRadioFormField = Storage::disk('local')->get('ComponentRadioFormField.txt');
		$componentCheckboxFormField = Storage::disk('local')->get('ComponentCheckboxFormField.txt');
		$componentRelationFieldContents = Storage::disk('local')->get('ComponentRelationFormField.txt');
	
		$fieldHelp = '&nbsp;<a href="#" class="cstooltip" data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote." tabindex="-1"><i class="ph ph-question"></i></a>';
		$modalSize = "";
		$fieldsPerRow = $jsonObject["form_fields_per_row"];
		$fieldSize = "";
		switch ($fieldsPerRow) {
			case 2:
				$fieldSize = "6";
				break;
			case 3:
				$modalSize = "modal-lg";
				$fieldSize = "4";
				break;
			case 4:
				$modalSize = "modal-xl";
				$fieldSize = "3";
				break;
		}
		$allAddEditColumns = "";
		for ($i=0; $i < count($fillableColumns); $i++) {
			if( $i % $fieldsPerRow == 0 )
				$allAddEditColumns .= "<div class='row mb-4'>\n";
			$fillableColumn = $fillableColumns[$i];
			$allAddEditColumns .= $componentAddEditComponent;
			switch( $fillableColumn["form_type"] ){
				case 'select':
					if( $fillableColumn["frm_select_type"] == "relation" || $fillableColumn["frm_select_type"] == "fixed" )
					$allAddEditColumns = str_replace('{{fieldData}}', $componentRelationFieldContents, $allAddEditColumns);
					$allAddEditColumns = str_replace('{{columnHelp}}', "", $allAddEditColumns);
					if( isset($fillableColumn["frm_popup_select_default_option"])
						&& isset($fillableColumn["frm_popup_select_default_option_id"]) && strlen($fillableColumn["frm_popup_select_default_option_id"]) > 0 
						&& isset($fillableColumn["frm_popup_select_default_option_value"]) && strlen($fillableColumn["frm_popup_select_default_option_value"]) > 0){
						$allAddEditColumns = str_replace('{{defaultOption}}', "\n".'<option value="'.$fillableColumn["frm_popup_select_default_option_id"].'" v-else>'.$fillableColumn["frm_popup_select_default_option_value"].'</option>', $allAddEditColumns);
					}
					else
						$allAddEditColumns = str_replace('{{defaultOption}}', "", $allAddEditColumns);
					$allAddEditColumns = str_replace('{{formFieldClass}}', "", $allAddEditColumns);
					break;
					
				case 'textarea':
					$allAddEditColumns = str_replace('{{fieldData}}', $componentTextAreaFormField, $allAddEditColumns);
					$allAddEditColumns = str_replace('{{columnHelp}}', $fieldHelp, $allAddEditColumns);
					$allAddEditColumns = str_replace('{{formFieldClass}}', "", $allAddEditColumns);
					break;

				case 'fileupload':
					$allAddEditColumns = str_replace('{{fieldData}}', $componentFileFormField, $allAddEditColumns);
					// $allAddEditColumns = str_replace('{{columnHelp}}', $fileHelp, $allAddEditColumns);
					$allAddEditColumns = str_replace('{{columnHelp}}', "", $allAddEditColumns);
					$allAddEditColumns = str_replace('{{formFieldClass}}', "", $allAddEditColumns);
					break;

				case 'yesno':
					$fillableColumn["frm_options"] = [];
					array_push($fillableColumn["frm_options"], ["key" => 1, "value" => "Yes"]);
					array_push($fillableColumn["frm_options"], ["key" => 0, "value" => "No"]);
				case 'radio':
					$componentRadioFormFieldContents = "";
					foreach($fillableColumn["frm_options"] as $frmOption){
						$componentRadioFormFieldContents .= "".$componentRadioFormField;
						$componentRadioFormFieldContents = str_replace('{{optionId}}', $frmOption["key"], $componentRadioFormFieldContents);
						$componentRadioFormFieldContents = str_replace('{{optionTitle}}', $frmOption["value"], $componentRadioFormFieldContents);
					}
					$allAddEditColumns = str_replace('{{fieldData}}', $componentRadioFormFieldContents, $allAddEditColumns);
					$allAddEditColumns = str_replace('{{columnHelp}}', "", $allAddEditColumns);
					$allAddEditColumns = str_replace('{{formFieldClass}}', " class=\"d-flex flex-row gap-3\"", $allAddEditColumns);
					break;

				case 'checkbox':
					$allAddEditColumns = str_replace('{{fieldData}}', $componentCheckboxFormField, $allAddEditColumns);
					$allAddEditColumns = str_replace('{{columnHelp}}', "", $allAddEditColumns);
					$allAddEditColumns = str_replace('{{formFieldClass}}', "", $allAddEditColumns);
					break;

				default:
					$allAddEditColumns = str_replace('{{fieldData}}', $componentInputFormField, $allAddEditColumns);
					if( $fillableColumn["form_type"] == "text" )
						$allAddEditColumns = str_replace('{{columnHelp}}', $fieldHelp, $allAddEditColumns);
					else
						$allAddEditColumns = str_replace('{{columnHelp}}', "", $allAddEditColumns);
					$allAddEditColumns = str_replace('{{formFieldClass}}', "", $allAddEditColumns);
					break;
				
			}
			// else
			// 	$allAddEditColumns .= $componentInputFieldContents;
			$allAddEditColumns = str_replace('{{fieldWidth}}', $fieldSize, $allAddEditColumns);
			$allAddEditColumns = str_replace('{{columnName}}', $fillableColumn["name"], $allAddEditColumns);
			$allAddEditColumns = str_replace('{{columnHint}}', $fillableColumn["frm_hint"], $allAddEditColumns);
			$allAddEditColumns = str_replace('{{columnLabel}}', $fillableColumn["frm_label"], $allAddEditColumns);
			$allAddEditColumns = str_replace('{{objectName}}', $jsonObject["object_name"], $allAddEditColumns);
			$allAddEditColumns = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $allAddEditColumns);
			// relation related - fixed pending
			if( $fillableColumn["form_type"] == "select" ){
				if( $fillableColumn["frm_select_type"] == "relation" ){
					$allAddEditColumns = str_replace('{{relation}}', $fillableColumn["frm_related_model"], $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relation-camelcase}}', $this->getCamelCase($fillableColumn["name"]), $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relation-pascalcase}}', $this->getPascalCase($fillableColumn["name"]), $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relationTitle}}', $fillableColumn["related_to_model_title"], $allAddEditColumns);
				}
				else if( $fillableColumn["frm_select_type"] == "fixed" ){
					$allAddEditColumns = str_replace('{{relation}}', $fillableColumn["name"], $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relation-camelcase}}', $this->getCamelCase($fillableColumn["name"]), $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relation-pascalcase}}', $this->getPascalCase($fillableColumn["name"]), $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relationTitle}}', "title", $allAddEditColumns);
				}
			}
			else{
				$vEmptyZero = "";
				switch ($fillableColumn["form_type"]) {
					case 'email':
						$allAddEditColumns = str_replace('{{columnType}}', "email", $allAddEditColumns);
						break;
					
					case 'numeric':
					case 'decimal':
						$allAddEditColumns = str_replace('{{columnType}}', "number", $allAddEditColumns);//v-empty-zero
						$vEmptyZero = "v-empty-zero";
						break;
						
					case 'date':
					case 'datetime':
						$allAddEditColumns = str_replace('{{columnType}}', "date", $allAddEditColumns);
						break;

					case 'password':
						$allAddEditColumns = str_replace('{{columnType}}', "password", $allAddEditColumns);
						break;
						
					case 'telephone':
						$allAddEditColumns = str_replace('{{columnType}}', "tel", $allAddEditColumns);
						break;
						
					case 'url':
						$allAddEditColumns = str_replace('{{columnType}}', "url", $allAddEditColumns);
						break;
							
					default:
						$allAddEditColumns = str_replace('{{columnType}}', "text", $allAddEditColumns);
						break;
				}
				$allAddEditColumns = str_replace('{{v-empty-zero}}', $vEmptyZero, $allAddEditColumns);
			}
			$required = "";
			$requiredError = "";
			if( $fillableColumn['required'] ){
				$required = ' <span class="mandatory">*</span>';
				$requiredError = ' <span v-if="v$.{{objectName-lowercase}}ForAdd.'.$fillableColumn["name"].'.$error" class="mandatory ms-3">Mandatory</span>';
			}
			$allAddEditColumns = str_replace('{{columnRequired}}', $required, $allAddEditColumns);
			$allAddEditColumns = str_replace('{{columnRequiredError}}', $requiredError, $allAddEditColumns);
			if( $i % $fieldsPerRow == ($fieldsPerRow-1) )
				$allAddEditColumns .= "</div>\n";
		}
		if(count($fillableColumns) % $fieldsPerRow != 0 )
			$allAddEditColumns .= "</div>\n";
		$componentAddEditFormContents = str_replace('{{objectName}}', $jsonObject["object_name"], $componentAddEditFormContents);
		$componentAddEditFormContents = str_replace('{{objectName-uppercase}}', $jsonObject["object_name"], $componentAddEditFormContents);
		$componentAddEditFormContents = str_replace('{{modalSize}}', $modalSize, $componentAddEditFormContents);
		$componentAddEditFormContents = str_replace('{{fields}}', $allAddEditColumns, $componentAddEditFormContents);
		return $componentAddEditFormContents;
	}

	public function generateAddEditVueScriptContent($jsonObject, $fillableColumns){
	}

	public function generateTableContent($jsonObject, $tableColumns, $searchType){
		$listFields = "";
		foreach ($tableColumns as $tableColumn) {
			$listFields .= "{ title: '" . $tableColumn["tbl_label"] . "', ";
			switch($tableColumn["table_type"]){
				case "relation":
					$listFields .= "property: '" . $tableColumn["tbl_relation_method"] . ".".$tableColumn["related_to_model_title"]."', alt_value: '".$tableColumn['text_if_empty']."', ";
					break;
				
				case "textoptions":
					$listFields .= "property: '" . $tableColumn["name"] . "', ";
					$sourceEnum = "enum: { ";
					foreach($tableColumn["tbl_options"] as $option){
						$sourceEnum .= "'".$option["key"]."': '".$option["value"]."', ";
					}
					$sourceEnum .= " }, ";
					$listFields .= $sourceEnum;
					break;
					
				case "datetime":
					$listFields .= "property: '" . $tableColumn["name"] . "";
					break;

				case "text":
					$listFields .= "property: '" . $tableColumn["name"] . "', ";
					break;
			}
			if ($tableColumn["sortable"])
				$listFields .= "sortable: true, ";
			if ($tableColumn["suffix"] && strlen($tableColumn["suffix"]) > 0)
				$listFields .= "suffix: ' " . $tableColumn["suffix"] . "', ";
			if ($tableColumn["table_type"] == "date")
				$listFields = "date_type: 'mysqldate', display_type: 'date', format: 'LLL dd, yyyy', ";
			$listFields .= "},\n";
		}
		$advancedSearchContent = "";
		if( $searchType == "advanced" ){
			$advancedSearchContent = Storage::disk('local')->get('ComponentAdvSearch.txt');
			$advancedSearchColumnContent = Storage::disk('local')->get('ComponentAdvSearchColumn.txt');
			$allSearchableColumns = "";
			foreach ($tableColumns as $tableColumn) {
				if( $tableColumn["searchable"] && array_search($tableColumn["table_type"], ["text", "textoptions", "relation", "datetime"]) !== FALSE ){
					$allSearchableColumns .= $advancedSearchColumnContent;
					$allSearchableColumns = str_replace('{{columnLabel-uppercase}}', strtoupper($tableColumn["tbl_label"]), $allSearchableColumns);
					$allSearchableColumns = str_replace('{{columnName}}', $tableColumn["name"], $allSearchableColumns);
					// Check the column type
					$advColumnType = "text";
					$minMaxValues = "";
					$sourceEnum = "";
					if( $tableColumn["table_type"] == "text" ){
						if($tableColumn["form_type"] == "numeric" || $tableColumn["form_type"] == "decimal" ){
							$advColumnType = "number";
							$minMaxValues = "min: 0, max: 999999,";
						}
					}
					else if( $tableColumn["table_type"] == "datetime" ){
						if( $tableColumn["form_type"] == "date" || $tableColumn["form_type"] == "datetime" )
							$advColumnType = "date";
					}
					else if( $tableColumn["table_type"] == "textoptions" ){
						$advColumnType = "dropdown";
						if( isset($tableColumn["tbl_options"]) && count($tableColumn["tbl_options"]) > 0 ){
							$sourceEnum = "source_enum: [ ";
							foreach($tableColumn["tbl_options"] as $option){
								$sourceEnum .= "{ id: '".$option["key"]."', value: '".$option["value"]."' }, ";
							}
							$sourceEnum .= " ]\n";
						}
					}
					else if( $tableColumn["table_type"] == "relation" ){
						$advColumnType = "date";
					}
				}
			}
		}
		return ["fieldsList" => $listFields, "advancedSearchContent" => $advancedSearchContent];
	}

	public function generatePopupComponentViewContent($jsonObject, $viewColumns){
		$componentViewRecordContents = Storage::disk('local')->get('ComponentViewRecord.txt');
		$componentViewField = Storage::disk('local')->get('ComponentViewField.txt');
		$componentRadioField = Storage::disk('local')->get('ComponentRadioField.txt');
		$componentViewBadge = Storage::disk('local')->get('ComponentViewBadge.txt');
		$modalSize = "";
		$fieldsPerRow = $jsonObject["form_fields_per_row"];
		$fieldSize = "";
		switch ($fieldsPerRow) {
			case 2:
				$fieldSize = "6";
				break;
			case 3:
				$modalSize = "modal-lg";
				$fieldSize = "4";
				break;
			case 4:
				$modalSize = "modal-xl";
				$fieldSize = "3";
				break;
		}
		//<span>{{ read{{objectName}}.{{fieldName}} }}</span>
		//<span v-if="read{{objectName}}.{{fieldName}}=={{key}}">{{value}}</span> - table_type
		//{{ read{{objectName}}.{{fieldDetails}} }}
		$allViewColumns = "";
		for ($i=0; $i < count($viewColumns); $i++) {
			$viewColumn = $viewColumns[$i];
			if( $viewColumn["use_in_table_view"] && $viewColumn["use_in_view"] ){
				if( $i % $fieldsPerRow == 0 )
					$allViewColumns .= "<div class='row mb-4'>\n";
				$allViewColumns .= $componentViewField;
				if( $viewColumn["badge"] == true ){
					$allViewColumns = str_replace('{{fieldData}}', $componentViewBadge, $allViewColumns);
				}
				else{
					if( $viewColumn["table_type"] == "textoptions" ){
						$allOptionsContent = "";
						foreach($viewColumn["tbl_options"] as $viewColumnOption){
							$allOptionsContent .= '<span v-if="read{{objectName}}.{{fieldName}}==\''.$viewColumnOption["key"].'\'">'.$viewColumnOption["value"]."</span>\n";
						}
						$allViewColumns = str_replace('{{fieldData}}', $allOptionsContent, $allViewColumns);
					}
					else if( $viewColumn["table_type"] == "relation" ){
						$allViewColumns = str_replace('{{fieldData}}', "<span v-if='read{{objectName}}.".$viewColumn["tbl_relation_method"]."?.".$viewColumn["related_to_model_title"]."'>{{ read{{objectName}}.".$viewColumn["tbl_relation_method"]."?.".$viewColumn["related_to_model_title"]." }}</span><span v-else><i>Not specified</i></span>", $allViewColumns);
					}
					if( $viewColumn["table_type"] == "yesno" ){
						$allOptionsContent .= '<span v-if="read{{objectName}}.{{fieldName}}==1">Yes'."</span>\n";
						$allOptionsContent .= '<span v-if="read{{objectName}}.{{fieldName}}==0">No'."</span>\n";
					}
					else
						$allViewColumns = str_replace('{{fieldData}}', "<span v-if='read{{objectName}}.".$viewColumn["name"]."'>{{ read{{objectName}}.".$viewColumn["name"]." }}</span><span v-else><i>Not specified</i></span>", $allViewColumns);
				}
				$allViewColumns = str_replace('{{fieldWidth}}', $fieldSize, $allViewColumns);
				$allViewColumns = str_replace('{{fieldName}}', $viewColumn["name"], $allViewColumns);
				$allViewColumns = str_replace('{{fieldLabel}}', $viewColumn["tbl_label"], $allViewColumns);
				$allViewColumns = str_replace('{{objectName}}', $jsonObject["object_name"], $allViewColumns);
				if( $i % $fieldsPerRow == ($fieldsPerRow-1) )
					$allViewColumns .= "</div>\n";
			}
		}
		if (count($viewColumns) % $fieldsPerRow != 0)
			$allViewColumns .= "</div>\n";
		$componentViewRecordContents = str_replace('{{objectName}}', $jsonObject["object_name"], $componentViewRecordContents);
		$componentViewRecordContents = str_replace('{{objectName-uppercase}}', $jsonObject["object_name"], $componentViewRecordContents);
		$componentViewRecordContents = str_replace('{{fields}}', $allViewColumns, $componentViewRecordContents);
		$componentViewRecordContents = str_replace('{{modalSize}}', $modalSize, $componentViewRecordContents);
		return $componentViewRecordContents;
	}

	public function generateReadmeContent($jsonObject, $formRelationshipColumns){
		$readmeContents = Storage::disk('local')->get('README');
		$readmeContents = str_replace('{{objectName}}', $jsonObject["object_name"], $readmeContents);
		$readmeContents = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $readmeContents);
		$componentMixinLoadRelationContents = "";
		$componentMixinContents = "";
		if( count($formRelationshipColumns) > 0 ){
			$componentMixinContents = Storage::disk('local')->get('MastersMixin.txt');
			$componentMixinRelationTemplate = Storage::disk('local')->get('ComponentMixinLoadRelation.txt');
			$componentMixinLoadRelationContents = "";
			foreach ($formRelationshipColumns as $formColumn) {
				$componentMixinLoadRelationContents .= $componentMixinRelationTemplate;
				$componentMixinLoadRelationContents = str_replace('{{relation}}', $formColumn["frm_related_model"], $componentMixinLoadRelationContents);
				$componentMixinLoadRelationContents = str_replace('{{relation-lowercase}}', strtolower($formColumn["frm_related_model"]), $componentMixinLoadRelationContents);
				$componentMixinLoadRelationContents .= "\n\n";
			}
			$readmeContents .= "\n\n|------------------------|\n".
				"|----M--I--X--I--N--S----|\n".
				"|------------------------|\n".
				"\n1. Please copy these functions in the mixins - masters.js, located at resources/js/mixins/masters.js within your project.".
				"\n2. In case, you haven't created this mixin already, please create it at the above location. A sample is available in this ZIP file at the location resources/js/mixins/masters.js.".
				"\n\n".$componentMixinLoadRelationContents;
			$componentMixinContents = str_replace('{{methods}}', $componentMixinLoadRelationContents, $componentMixinContents);
		}
		return ["readme" => $readmeContents, "mixin" => $componentMixinContents];
	}

	public function getGeneratedCode(Request $request){
		$input = $request->all();
		log::info($input);
		$generatedModule = new \App\Models\GeneratedModule();
		$generatedModule->code = ''.time();
		$generatedModule->object = json_encode($input);
		$generatedModule->save();
		return response()->json(["code" => $generatedModule->code]);
	}
	/*
		if( isset($input["columns"]) && isset($input["object_name"]) && isset($input["add_edit_mode"])){
			$columns = $input["columns"];
			$filteredFormColumns = [];
			$filteredTableColumns = [];
			$multipleFilesColumns = [];
			// $columnCounter = 0;
			foreach($columns as $column){
				if( $column["use_in_form"] ){
					$formColumnInstruction = "\t\tForms\\Components\\";
					$formAddl = "";
					$required = "";
					$length = "";
					$prefixSuffix = "";
					$mask = "";
					// $dateDisplayFormat = "";
					// $dateFormat = "";
					// Handle type
					switch($column["form_type"]){
						case "text":
							$formColumnInstruction .= "TextInput::make('".$column["name"]."')";
							break;
						case "email":
							$formColumnInstruction .= "TextInput::make('".$column["name"]."')";
							$formAddl = "->email()";
							break;
						case "numeric":
							$formColumnInstruction .= "TextInput::make('".$column["name"]."')";
							$formAddl = "->numeric()";
							break;
						case "decimal":
							$formColumnInstruction .= "TextInput::make('".$column["name"]."')";
							$formAddl = "->numeric()->inputMode('decimal')";
							break;
						case "telephone":
							$formColumnInstruction .= "TextInput::make('".$column["name"]."')";
							$formAddl = "->tel()";
							break;
						case "url":
							$formColumnInstruction .= "TextInput::make('".$column["name"]."')";
							$formAddl = "->url()";
							break;
						case "password":
							$formColumnInstruction .= "TextInput::make('".$column["name"]."')";
							$formAddl = "->password()->autocomplete(false)";
							break;
						case "textarea":
							$formColumnInstruction .= "Textarea::make('".$column["name"]."')";
							$formAddl = "";
							break;
						case "richeditor":
							$formColumnInstruction .= "RichEditor::make('".$column["name"]."')";
							$formAddl = "";
							break;
						case "date":
							$formColumnInstruction .= "DatePicker::make('".$column["name"]."')";
							$formAddl = "";
							if( strlen($column["date_display_format"]) > 0 )
								$formAddl = "displayFormat('".$column["date_display_format"]."')";
							else
								$formAddl = "displayFormat('M d, Y')";
							if( strlen($column["date_return_fmt"]) > 0 )
								$formAddl = "displayFormat('".$column["date_return_fmt"]."')";
							else
								$formAddl = "format('Y-m-d')";
							break;
						case "toggle":
							$formColumnInstruction .= "Toggle::make('".$column["name"]."')->inline(false)->onColor('success')";
							break;
						case "radio":
							$formColumnInstruction .= "Radio::make('".$column["name"]."')";
							if( $column["frm_select_type"] == "fixed"){
								if( count($column["frm_options"]) > 0 ){
									$formAddl = "->options([";
									foreach ($column["frm_options"] as $option) {
										$formAddl .= "'".$option["key"]."'=>'".$option["value"]."',";
									}
									$formAddl .= "])";
								}
							}
							else if( $column["frm_select_type"] == "relation"){
								$formAddl = "->relationship(name: '".$column["frm_relation_method"]."', titleAttribute: '".$column["frm_title_attribute"]."')";
							}
							break;
						case "select":
							$formColumnInstruction .= "Select::make('".$column["name"]."')";
							if( $column["frm_select_type"] == "fixed"){
								if( count($column["frm_options"]) > 0 ){
									$formAddl = "->options([";
									foreach ($column["frm_options"] as $option) {
										$formAddl .= "'".$option["key"]."'=>'".$option["value"]."',";
									}
									$formAddl .= "])";
								}
							}
							else if( $column["frm_select_type"] == "relation"){
								$formAddl = "->relationship(name: '".$column["frm_relation_method"]."', titleAttribute: '".$column["frm_title_attribute"]."')";
							}
							else if( $column["frm_select_type"] == "pluck"){
								if( isset($column["frm_related_model"]) 
								&& isset($column["frm_pluck_id_attribute"]) && isset($column["frm_pluck_title_attribute"]) 
								&& isset($column["frm_pluck_default_option"]) 
								&& isset($column["frm_pluck_default_option_id"]) && strlen($column["frm_pluck_default_option_id"]) > 0 
								&& isset($column["frm_pluck_default_option_value"]) && strlen($column["frm_pluck_default_option_value"]) > 0 ){
									$pluckOptions = str_replace("###PLUCKMODEL###", $column["frm_related_model"], $pluckOptions);
									$pluckOptions = str_replace("###PLUCKMODELID###", $column["frm_pluck_id_attribute"], $pluckOptions);
									$pluckOptions = str_replace("###PLUCKMODELTITLE###", $column["frm_pluck_title_attribute"], $pluckOptions);
									if( $column["frm_pluck_default_option"] == true ){
										$pluckOptions = str_replace("###DEFAULTOPTION###", 
											"'".$column["frm_pluck_default_option_id"]."' => '".$column["frm_pluck_default_option_value"]."'", 
											$pluckOptions);
									}
									else{
										$pluckOptions = str_replace("###DEFAULTOPTION###", "", $pluckOptions);
									}
								}
								$formAddl = $pluckOptions;
							}
							break;
						case "fileupload":
							$formColumnInstruction .= "FileUpload::make('".$column["name"]."')";
							if( isset($column["frm_multiple_uploads"]) && $column["frm_multiple_uploads"] == true ){
								$formColumnInstruction .= "->multiple()";
								array_push($multipleFilesColumns, "'".$column["name"]."' => 'array',");
							}
							break;
					}
					// Hint
					if( isset($column["frm_hint"]) && strlen(trim($column["frm_hint"])) > 0 )
						$formColumnInstruction .= "->hint('".trim($column["frm_hint"])."')";
					// Label
					if( strlen(trim($column["frm_label"])) > 0 )
						$formColumnInstruction .= "->label('".trim($column["frm_label"])."')";
					// required
					if( $column["required"] )
						$required = "->required()";
					// length
					if( is_numeric($column["length"]) && $column["length"] > 0 )
						$length = "->length(".$column["length"].")";
					else{
						$length = "";
						if( is_numeric($column["min_length"]) && $column["min_length"] > 0 )
							$length .= "->minLength(".$column["min_length"].")";
						if( is_numeric($column["max_length"]) && $column["max_length"] > 0 )
							$length .= "->maxLength(".$column["max_length"].")";
					}
					// prefix/suffix
					if( strlen(trim($column["prefix"])) > 0 )
						$prefixSuffix = "->prefix('".trim($column["prefix"])."')";
					if( strlen(trim($column["suffix"])) > 0 )
						$prefixSuffix .= "->suffix('".trim($column["suffix"])."')";
					/// Mask
					if( strlen(trim($column["mask"])) > 0 )
						$mask = "->mask('".trim($column["mask"])."')";
					$formColumnInstruction .= $formAddl.$required.$length.$prefixSuffix.$mask.",";
					array_push($filteredFormColumns, $formColumnInstruction);
				}
				//
				if( $column["use_in_table"] ){
					$tableColumnInstruction = "\t\t\tTables\\Columns\\";
					$tableAddl = "";
					// Handle type
					switch($column["table_type"]){
						case "text":
							$tableColumnInstruction .= "TextColumn::make('".$column["name"]."')->default('Not Specified')";
							break;
						case "textoptions":
							$tableColumnInstruction .= "TextColumn::make('".$column["name"]."')";
							if( count($column["tbl_options"]) > 0 ){
								$tableAddl = '->state(function (\Illuminate\Database\Eloquent\Model $record): string {'."\n\t\t\t\t";
								$tableAddl .= 'switch($record["'.$column["name"].'"]){';
								foreach ($column["tbl_options"] as $option) {
									$tableAddl .= "\n\t\t\t\t\tcase '".$option["key"]."': return '".$option["value"]."';";
								}
								$tableAddl .= "\n\t\t\t\t}\n\t\t\t})";
								$tableColumnInstruction .= $tableAddl;
							}
							break;
						case 'relation':
							$tableColumnInstruction .= "TextColumn::make('".$column["tbl_relation_method"].".".$column["tbl_title_attribute"]."')";
							break;
						case "icon":
							$tableColumnInstruction .= "IconColumn::make('".$column["name"]."')->boolean()->alignment(\Filament\Support\Enums\Alignment::Center)";
							break;
						case "datetime":
							$tableColumnInstruction .= "TextColumn::make('".$column["name"]."')->default('Not Specified')->alignment(\Filament\Support\Enums\Alignment::Center)";
							// Date Format
							if( isset($column["tbl_date_fmt"]) ){
								if( $column["tbl_date_fmt"] == 1 )
									$tableColumnInstruction .= "->date()";
								else if( $column["tbl_date_fmt"] == 2 )
									$tableColumnInstruction .= "->dateTime()";
							}
							break;
						case "fileupload":
							$tableColumnInstruction .= "ImageColumn::make('".$column["name"]."')->height(50)->defaultImageUrl(url('/images/placeholder.png'))";
							break;
					}
					if( strlen(trim($column["tbl_label"])) > 0 )
						$tableColumnInstruction .= "->label('".trim($column["tbl_label"])."')";
					if( strlen($column["text_if_empty"]) > 0 )
						$tableColumnInstruction .= "->default('".$column["text_if_empty"]."')";
					if( $column["searchable"] == true )
						$tableColumnInstruction .= "->searchable()";
					if( $column["sortable"] == true )
						$tableColumnInstruction .= "->sortable()";
					if( $column["badge"] == true )
						$tableColumnInstruction .= "->badge()";
					if( $column["use_currency"] == true )
						$tableColumnInstruction .= "->money('".$column['currency_symbol']."')";
					if( $column["can_copy"] == true )
						$tableColumnInstruction .= "->copyable()->copyMessage('Copied to Clipboard')->copyMessageDuration(1500)";
					$tableColumnInstruction .= ",";
					array_push($filteredTableColumns, $tableColumnInstruction);
				}
			}
			$response["form_content"] = str_replace("###FORMCOLUMNS###", implode("\n", $filteredFormColumns), $formContent);
			$tableContent = str_replace("###TABLECOLUMNS###", implode("\n", $filteredTableColumns), $tableContent);
			// Manage Activate Deactivate
			if( isset($input["enable_activate_deactivate"]) && $input["enable_activate_deactivate"] == true ){
				if( isset($input["activate_deactivate_column"]) && strlen($input["activate_deactivate_column"]) > 0 ){
					$activateDeactiveFunc = str_replace("###ACTDEACTCOL###", $input["activate_deactivate_column"], $activateDeactiveFunc);
					$tableContent = str_replace("###ACTDEACTFUNC###", $activateDeactiveFunc, $tableContent);
				}
				else{
					$tableContent = str_replace("###ACTDEACTFUNC###", "", $tableContent);
					$tableContent = str_replace("###ACTDEACTCOL###", "", $tableContent);
				}
			}
			else{
				$tableContent = str_replace("###ACTDEACTFUNC###", "", $tableContent);
				$tableContent = str_replace("###ACTDEACTCOL###", "", $tableContent);
			}
			// ###ACTDEACTFUNC###$activateDeactiveFunc###ACTDEACTCOL###
			$response["table_content"] = $tableContent;
			$response["objcount_content"] = $objCountContent;
			$objectName = $input["object_name"];
			$response["multiplefiles_content"] = str_replace("###MULTIPLEFILES###", implode("\n", $multipleFilesColumns), $multipleFiles);
			$response["status"] = 1;
		}
		else
			$response["status"] = -1;
		return response()->json($response);
	}*/

	public function saveConfig(Request $request){
		$input = $request->all();
		// log::info($input);
		// return response()->json(["status" => 1]);
		$response = [];
		if( isset($input["db"]) && isset($input["tbl"]) && isset($input["object"]) && isset($input["object"]["columns"])){
			\App\Models\ObjectConfig::updateOrCreate(
				[ 'object_settings' => json_encode($input["object"]) ],
				[ 'db_name' => $input["db"], 'table_name' => $input["tbl"] ]
			);
			// log::info($objectConfig);
			$response["status"] = 1;
		}
		else
			$response["status"] = -1;
		return response()->json($response);
	}

	public function loadConfig(Request $request){
		$input = $request->all();
		$response = [];
		if( isset($input["db"]) && isset($input["tbl"]) ){
			$_objectConfig = \App\Models\ObjectConfig::where('db_name', $input["db"])->where('table_name', $input["tbl"])->first();
			if( $_objectConfig !== null ){
				$objectConfig = $_objectConfig->toArray();
				$objectConfig["object_config"] = json_decode($objectConfig["object_settings"], false);
				unset($objectConfig["object_settings"]);
				$response["object"] = $objectConfig;
				$response["status"] = 1;
			}
			else{
				$response["status"] = 0;
			}
		}
		else
			$response["status"] = -1;
		return response()->json($response);
	}

	public function getCamelCase($string){
		return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
	}
	
	public function getPascalCase($string){
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
	}
}
