<?php
namespace BriqueAdminCreator;

use Illuminate\Support\Facades\Log;

class ModelService{

	public static function generateModelContent($objectName, $tableName, $allColumns){
		// Create relationships
		$relationships = "";
		$modelRelationshipsTemplate = file_get_contents(__DIR__.'/storage/app/model/Model_RelationshipFunction.txt');
		$fillableColumns = [];
		foreach ($allColumns as $column) {
			if(!in_array($column["name"], ['created_at', 'updated_at', 'deleted_at']))
				array_push($fillableColumns, $column["name"]);
			//related_model, related_model_id
			if( isset($column["table_view"]) && isset($column["table_view"]["type"]) && $column["table_view"]["type"]== 'relation'){
				if( isset($column["relation"]["method"]) && $column["relation"]["method"] != null && strlen($column["relation"]["method"]) > 0 
				&& isset($column["relation"]["related_model"]) && $column["relation"]["related_model"] != null && strlen($column["relation"]["related_model"]) > 0
				&& isset($column["relation"]["related_model_id"]) && $column["relation"]["related_model_id"] != null && strlen($column["relation"]["related_model_id"]) > 0 ){
					$relationships .= $modelRelationshipsTemplate."\n\n";
					$relationships = str_replace('{{objectName}}', $objectName, $relationships);
					$relationships = str_replace('{{method}}', $column["relation"]["method"], $relationships);
					$relationships = str_replace('{{related_model}}', $column["relation"]["related_model"], $relationships);
					$relationships = str_replace('{{id_attribute}}', $column["relation"]["id_attribute"], $relationships);
					$relationships = str_replace('{{related_model_id}}', $column["relation"]["related_model_id"], $relationships);
				}
			}
		}
		$modelContents = file_get_contents(__DIR__.'/storage/app/model/Model.txt');
		$modelContents = str_replace('{{objectName}}', $objectName, $modelContents);
		$modelContents = str_replace('{{table_name}}', $tableName, $modelContents);
		// Append status column
		// array_push($fillableColumns, $jsonObject["activate_deactivate_column"]);
		$modelContents = str_replace('{{all_columns}}', "'".implode("', '", $fillableColumns)."'", $modelContents);
		$modelContents = str_replace('{{relationships}}', $relationships, $modelContents);
		return $modelContents;
	}

	// Resource for the Model
	public static function generateModelResourceContent($objectName, $allColumns, $authRequired){
		$authActionsTrue = "
		if (isset(\$input['current_user_id']) && \$input['current_user_id'] > 0) {
			\$currentUser = \App\Models\User::find(\$input['current_user_id']);
			\$actions = ActionsService::generateActions(\App\Models\\".$objectName."::class, \$currentUser->role_id, \$this->status);
		};";
		$authActionsFalse = "
			// If authorization is not required then show all btns
			\$actions = ActionsService::generateActions(\App\Models\\".$objectName."::class, 1, \$this->status);";

		$modelResourceContents = file_get_contents(__DIR__.'/storage/app/model/ModelResource.txt');
		$modelResourceContents = str_replace('{{objectName}}', $objectName, $modelResourceContents);
		$resourceColumns = "";
		$fillableColumns = [];
		foreach ($allColumns as $column) {
			$resourceColumns .= "'".$column["name"]."' => \$this->".$column["name"].",\n";
			if($column["form"]["type"] == 'relation')
				$resourceColumns .= "'".$column["relation"]["method"]."' => \$this->".$column["relation"]["method"].",\n";
		}
		$modelResourceContents = str_replace('{{resource-fields}}', $resourceColumns, $modelResourceContents);
		if( $authRequired ){
			$modelResourceContents = str_replace('{{auth-actions}}', $authActionsTrue, $modelResourceContents);
		}else{
			$modelResourceContents = str_replace('{{auth-actions}}', $authActionsFalse, $modelResourceContents);
		}
		return $modelResourceContents;
	}

	public static function generateWebRouteContent($jsonObject){
		$routeTemplate = file_get_contents(__DIR__.'/storage/app/WebRoute.txt');
		$objectName = str_replace(' ', '', $jsonObject["object_name"]);
		$routeTemplate = str_replace("{{objectName}}", $objectName, $routeTemplate);
		$routeTemplate = str_replace("{{objectName-lowercase}}", strtolower($objectName), $routeTemplate);
		if($jsonObject["form_mode"] === 'different_page'){
			$routeTemplate .= "Route::get('/".strtolower($objectName)."/add', [App\Http\Controllers\\".$objectName."Controller::class, 'add'])->name('add-".strtolower($objectName)."-page');\n";
		}
		if($jsonObject["view_mode"] === 'different_page'){
			$routeTemplate .= "Route::get('/".strtolower($objectName)."/view/{id}', [App\Http\Controllers\\".$objectName."Controller::class, 'view'])->name('view-".strtolower($objectName)."-page');\n";
		}
		return $routeTemplate;
	}

	public static function generateExportContent($objectName, $allColumns){
		$exportContents = file_get_contents(__DIR__.'/storage/app/model/Export.txt');
		$exportContents = str_replace('{{objectName}}', $objectName, $exportContents);
		$exportColumnLabels = "";
		$exportMapColumns = "";
		foreach ($allColumns as $column) {
			if( $column["table_view"]["use_in_download"] == true ){
				$exportColumnLabels .= "'".$column['table_view']['label']."',";
				if( $column["table_view"]["type"] == 'relation' ){
					$exportMapColumns .= "\$row->".$column['relation']['method']."->".$column['relation']['title_attribute'].",\n";
				}else{
					$exportMapColumns .= "\$row->".$column['name'].",\n";
				}
			}
		}
		$exportContents = str_replace('{{exportColumnLabels}}', $exportColumnLabels, $exportContents);
		$exportContents = str_replace('{{exportMapColumns}}', $exportMapColumns, $exportContents);
		return $exportContents;
	}

	public static function generateReadmeContent($jsonObject, $allColumns){
		$formRelationshipColumns = [];
		foreach ($allColumns as $column) {
			if( isset($column["form"]["type"]) && $column["form"]["type"]== 'relation'){
				array_push($formRelationshipColumns, $column);
			}
		}
		$readmeContents = file_get_contents(__DIR__.'/storage/app/README');
		$readmeContents = str_replace('{{objectName}}', $jsonObject["object_name"], $readmeContents);
		$readmeContents = str_replace('{{objectLabel}}', $jsonObject["object_label"], $readmeContents);
		$readmeContents = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $readmeContents);
		$componentMixinLoadRelationContents = "";
		$componentMixinContents = "";
		// To search for later
		$mixinSearchForDef = "loadAll{{relation}}";
		$componentMixinRelations = [];
		$componentSearchForImport = 'import {{objectName}}Component from "./components/{{objectName}}Component";';
		$componentSearchForDecl = 'app.component("{{objectName-lowercase}}-component", {{objectName}}Component);';
		if( count($formRelationshipColumns) > 0 ){
			$componentMixinContents = file_get_contents(__DIR__.'/storage/app/MastersMixin.txt');
			$componentMixinRelationTemplate = file_get_contents(__DIR__.'/storage/app/component/ComponentMixinLoadRelation.txt');
			$componentMixinLoadRelationContents = "";
			foreach ($formRelationshipColumns as $formColumn) {
				$componentMixinLoadRelationContent = $componentMixinRelationTemplate;
				$componentMixinLoadRelationContent = str_replace('{{relation}}', $formColumn["relation"]["related_model"], $componentMixinLoadRelationContent);
				$componentMixinLoadRelationContent = str_replace('{{relation-lowercase}}', strtolower($formColumn["relation"]["related_model"]), $componentMixinLoadRelationContent);

				// Append this to the overall file
				$componentMixinLoadRelationContents .= $componentMixinLoadRelationContent;
				$componentMixinLoadRelationContents .= "\n\n";
				
				// Important
				$componentMixinRelations[str_replace('{{relation}}', $formColumn["relation"]["related_model"], $mixinSearchForDef)] = $componentMixinLoadRelationContent;
			}
			$readmeContents .= 
				"\n\n|------------------------|\n".
				"|----M--I--X--I--N--S----|\n".
				"|------------------------|\n".
				"\n1. Please copy these functions in the mixins - masters.js, located at resources/js/mixins/masters.js within your project.".
				"\n2. In case, you haven't created this mixin already, please create it at the above location. A sample is available in this ZIP file at the location resources/js/mixins/masters.js.".
				"\n\n".$componentMixinLoadRelationContents;
			$componentMixinContents = str_replace('{{methods}}', $componentMixinLoadRelationContents, $componentMixinContents);
		}

		// Important
		$componentSearchForImport = str_replace('{{objectName}}', $jsonObject["object_name"], $componentSearchForImport);
		$componentSearchForDecl = str_replace('{{objectName}}', $jsonObject["object_name"], $componentSearchForDecl);
		$componentSearchForDecl = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $componentSearchForDecl);

		return [
			"readme" => $readmeContents, 
			"mixin" => $componentMixinContents,
			"mixin_relations" => $componentMixinRelations,
			"component_searchfor_import" => $componentSearchForImport,
			"component_searchfor_decl" => $componentSearchForDecl
		];
	}
}