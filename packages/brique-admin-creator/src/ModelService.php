<?php
namespace BriqueAdminCreator;

class ModelService{

	public static function generateModelContent($jsonObject, $fillableColumns, $relationshipColumns){
		// Create relationships
		$relationships = "";
		$modelRelationshipsContents = file_get_contents(__DIR__.'/storage/app/Model_RelationshipFunction.txt');
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
		$modelContents = file_get_contents(__DIR__.'/storage/app/Model.txt');
		$modelContents = str_replace('{{objectName}}', $jsonObject["object_name"], $modelContents);
		$modelContents = str_replace('{{table_name}}', $jsonObject["tbl"], $modelContents);
		// Append status column
		array_push($fillableColumns, $jsonObject["activate_deactivate_column"]);
		$modelContents = str_replace('{{all_columns}}', "'".implode("', '", $fillableColumns)."'", $modelContents);
		$modelContents = str_replace('{{relationships}}', $relationships, $modelContents);
		return $modelContents;
	}

	public static function generateControllerContent($jsonObject, $searchColumns, $requiredColumns, $uniqueColumn, $tableRelationshipColumns){
		$controllerContents = file_get_contents(__DIR__.'/storage/app/Controller.txt');
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
				$requiredColumnsCondition .= "|decimal:0,2";
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
			$uniqueColumnContents = file_get_contents(__DIR__.'/storage/app/ControllerUniqueCheck.txt');
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

	public static function generateExportContent($jsonObject, $requiredColumns){
		$exportContents = file_get_contents(__DIR__.'/storage/app/Export.txt');
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

	public static function generateReadmeContent($jsonObject, $formRelationshipColumns){
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
			$componentMixinRelationTemplate = file_get_contents(__DIR__.'/storage/app/ComponentMixinLoadRelation.txt');
			$componentMixinLoadRelationContents = "";
			foreach ($formRelationshipColumns as $formColumn) {
				$componentMixinLoadRelationContent = $componentMixinRelationTemplate;
				$componentMixinLoadRelationContent = str_replace('{{relation}}', $formColumn["frm_related_model"], $componentMixinLoadRelationContent);
				$componentMixinLoadRelationContent = str_replace('{{relation-lowercase}}', strtolower($formColumn["frm_related_model"]), $componentMixinLoadRelationContent);

				// Append this to the overall file
				$componentMixinLoadRelationContents .= $componentMixinLoadRelationContent;
				$componentMixinLoadRelationContents .= "\n\n";
				
				// Important
				$componentMixinRelations[str_replace('{{relation}}', $formColumn["frm_related_model"], $mixinSearchForDef)] = $componentMixinLoadRelationContent;
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
