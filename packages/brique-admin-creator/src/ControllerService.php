<?php
namespace BriqueAdminCreator;

use Illuminate\Support\Facades\Log;

class ControllerService{
    // Controller
	public static function generateControllerContent($jsonObject){
		$objectName = $jsonObject['object_name'];
		$allColumns = [];
		foreach($jsonObject['columns'] as $column){
			if($column["type"] != 'divider')
				array_push($allColumns, $column);
		}
		$objectLabel = $jsonObject['object_label'];
		$controllerContents = file_get_contents(__DIR__.'/storage/app/controller/Controller.txt');
		$uploadFileClearImagesContents = file_get_contents(__DIR__.'/storage/app/controller/UploadFileClearImages.txt');
		$validateAndUploadedFileContents = file_get_contents(__DIR__.'/storage/app/controller/ValidateAndUploadFile.txt');
		$addEditContent = file_get_contents(__DIR__.'/storage/app/controller/AddEdit.txt');
		$viewContent = file_get_contents(__DIR__.'/storage/app/controller/View.txt');
		$templateAllUploadMethodsContent = file_get_contents(__DIR__.'/storage/app/controller/TemplateAllUploadMethods.txt');
		// Controller - replace tokens
		$controllerContents = str_replace('{{objectName}}', $objectName, $controllerContents);
		$controllerContents = str_replace('{{objectLabel}}', $objectLabel, $controllerContents);
		$controllerContents = str_replace('{{objectName-lowercase}}', strtolower($objectName), $controllerContents);
		$searchNotEquals = "";
		$searchEquals = "";
		$searchHas = "";
		$searchDoesntHave = "";
		$i = 0;
		$requiredColumnsCondition = "";
		// $withClause = "";
		// $withClause = "with(";
		$relations = [];
		$convertToJSON = "";
		$uploadFileColumns = [];
		$autoGenerateCondition = "";
		foreach ($allColumns as $column) {
			//check for searchable columns
			if( $column["table_view"] && $column["table_view"]["searchable"] == true ){
				$searchNotEquals .= "->whereNot('".$column["name"]."', trim(\$filter['search_for_value']))";
				$searchDoesntHave .= "->where('".$column["name"]."', 'not like', '%' . trim(\$filter['search_for_value']) . '%')";
				//whereDoesntHave
				if( $i++ == 0){
					$searchEquals = "where('".$column["name"]."', trim(\$filter['search_for_value']))";
					$searchHas = "where('".$column["name"]."', 'like', '%' . trim(\$filter['search_for_value']) . '%')";
				}
				else{
					$searchEquals .= "->orWhere('".$column["name"]."', trim(\$filter['search_for_value']))"; 
					$searchHas .= "->orWhere('".$column["name"]."', 'like', '%' . trim(\$filter['search_for_value']) . '%')";
				}
			}
			//Check for required columns
			if( $column["form"] && $column["form"]["required"] == true ){
				$requiredColumnsCondition .= "'".$column['name']."' => ['required'";
				if( $column["form"]["type"] == "text" || $column["form"]["type"] == "textarea" || $column["form"]["type"] == "richeditor" || $column["form"]["type"] == "password" || $column["form"]["type"] == "checkbox" )
					$requiredColumnsCondition .= ", 'string'";
				elseif( $column["form"]["type"] == "email" )
					$requiredColumnsCondition .= ", 'email:filter'";
				elseif( $column["form"]["type"] == "numeric" )
					$requiredColumnsCondition .= ", 'integer'";
				elseif( $column["form"]["type"] == "decimal" )
					$requiredColumnsCondition .= ", 'decimal:0,2'";
				elseif( $column["form"]["type"] == "telephone" )
					$requiredColumnsCondition .= ", 'min:6','max:12'";
				elseif( $column["form"]["type"] == "url" )
					$requiredColumnsCondition .= ", 'url:http,https'";
				elseif( $column["form"]["type"] == "date" )
					$requiredColumnsCondition .= ", 'date'";
				elseif( $column["form"]["type"] == "time" )
					$requiredColumnsCondition .= ", 'time'";
				elseif( $column["form"]["type"] == "fileupload" )
					$requiredColumnsCondition .= ", 'file'";
				// Check for unique
				if($column["is_unique"] == true){
					$requiredColumnsCondition .= ', Rule::unique(\App\Models\\'.$objectName.'::class)->ignore($'.strtolower($objectName).'["id"])';
				}
				$requiredColumnsCondition .= "],\n";
			}
			// Check for relationships
			if( $column["form"] && $column["form"]["type"] == 'relation'){
				$relations[] = "'".$column["relation"]["method"]."', ";
				// $convertToJSON .="\nif (isset($".strtolower($objectName)."['relation']) && is_array($".strtolower($objectName)."['relation'])) {
				// 	\$objectToSave['relation'] = json_encode($".strtolower($objectName)."['relation']);
				// }\n";
			}
			if( $column["form"] && $column["form"]["type"] == 'textoptions'){
				// $convertToJSON .="\nif (isset($".strtolower($objectName)."['".$column["name"]."']) && is_array($".strtolower($objectName)."['".$column["name"]."'])) {
				// 	\$objectToSave['".$column["name"]."'] = json_encode($".strtolower($objectName)."['".$column["name"]."']);
				// }\n";
			}
				// Check for file uploads
			if( $column["form"] && $column["form"]["type"] == 'fileupload'){
				array_push($uploadFileColumns, $column);
			}
			if( isset($column["form"]["autogenerate"]) && $column["form"]["autogenerate"] === true){
				$autoGenerateCondition .= "if( $".strtolower($objectName)."['".$column["name"]."']=== 'AUTOGENERATED'){\n\t\$objectToSave['".$column["name"]."'] = time();\n\t}\n";
			}
		}
		// $withClause .= ")->";
		// $withClause = substr($withClause, 0, strlen($withClause)-2);
		if (count($relations) > 0) {
			$withClause = 'with(' . implode(', ', $relations) . ')->';
		} else {
			$withClause = '';
		}
		$controllerContents = str_replace('{{autoGenerateCondition}}', $autoGenerateCondition, $controllerContents);
		$controllerContents = str_replace('{{advancedSearchColumns-like}}', $searchHas , $controllerContents);
		$controllerContents = str_replace('{{advancedSearchColumns-notlike}}', $searchDoesntHave , $controllerContents);
		$controllerContents = str_replace('{{advancedSearchColumns-notmatch}}', $searchNotEquals , $controllerContents);
		$controllerContents = str_replace('{{advancedSearchColumns-match}}', $searchEquals , $controllerContents);
		$controllerContents = str_replace('{{addRequiredColumns}}', $requiredColumnsCondition, $controllerContents);
		$controllerContents = str_replace('{{withClause}}', $withClause, $controllerContents);
		$controllerContents = str_replace('{{convertToJSON}}', $convertToJSON, $controllerContents);

		$clearMediaPart = "";
		$clearColumnImagesPart = "";
		$getImagePathPart = "";
		$setImageFieldNull = "";
		// $extractImagesPathPart = "";
		$fileIndex = 1;
		$allConditionsArray = [];
		$validateFields = "";
		$forClearFiles = "";
		foreach($uploadFileColumns as $column){
			$clearMediaPart .= "\$duplicateObject->".$column["name"]." = null;\n";
			$clearColumnImagesPart .= "\$this->clearUpload(".strtolower($objectName)."->".$column["name"].");\n";
			$getImagePathPart .= "if( \$fieldName == '".$column["name"]."' )\n\t\$_filePaths = $".strtolower($objectName)."->".$column["name"].";\ntry{\$filepaths=jsondecode( \$_filePaths );}catch(){}\n";
			$setImageFieldNull .= "if( \$fieldName == '".$column["name"]."' ){\n\t\$".strtolower($objectName)."->".$column["name"]." = null;}\n";
// 			$extractImagesPathPart .= <<<EXT
// if( \$strtolower(\$objectName)->\$column["name"] != null && strlen(\$strtolower(\$objectName)->\$column["name"]) > 0 && Storage::exists(\$strtolower(\$objectName)->\$column["name"]) )
// 	array_push(\$data["images"], \$strtolower(\$objectName)->\$column["name"]);
// EXT;
			array_push($allConditionsArray, 
				"(\$request->hasFile('uploaded_file_".$fileIndex."') && \$request->file('uploaded_file_".$fileIndex."')->isValid())");

			$validateFields .= $validateAndUploadedFileContents;
			$validateFields = str_replace('{{fileIndex}}', $fileIndex, $validateFields);
			$validateFields = str_replace('{{fieldName}}', $column["name"], $validateFields);
			$validateFields = str_replace('{{objectName-lowercase}}', strtolower($objectName), $validateFields);
			$fileIndex++;
		}
		$uploadFileClearImagesContents = str_replace('{{validateUploadedFileConditions}}', join(" || ", $allConditionsArray), $uploadFileClearImagesContents);
		$uploadFileClearImagesContents = str_replace('{{validateUploadedField}}', $validateFields, $uploadFileClearImagesContents);
		
		$templateAllUploadMethodsContent = str_replace('{{setImageFieldNull}}', $setImageFieldNull, $templateAllUploadMethodsContent);
		$templateAllUploadMethodsContent = str_replace('{{uploadFileClearImagesPart}}', $uploadFileClearImagesContents, $templateAllUploadMethodsContent);
		$templateAllUploadMethodsContent = str_replace('{{objectName}}', $objectName, $templateAllUploadMethodsContent);
		$templateAllUploadMethodsContent = str_replace('{{objectName-lowercase}}', strtolower($objectName), $templateAllUploadMethodsContent);

		$controllerContents = str_replace('{{clearMediaPart}}', $clearMediaPart, $controllerContents);
		$controllerContents = str_replace('{{clearColumnImagesPart}}', $clearColumnImagesPart, $controllerContents);
		$controllerContents = str_replace('{{getImagePathPart}}', $getImagePathPart, $controllerContents);
		$controllerContents = str_replace('{{exportToPDF}}', '', $controllerContents);

		if (!empty($uploadFileColumns) && count($uploadFileColumns) > 0) {
            $controllerContents = str_replace('{{templateAllUploadMethods}}', $templateAllUploadMethodsContent, $controllerContents);
        } else {
            $controllerContents = str_replace('{{templateAllUploadMethods}}', '', $controllerContents);
        }
		// $controllerContents = str_replace('{{extractImagesPathPart}}', $extractImagesPathPart, $controllerContents);

		if($jsonObject["form_mode"] == 'different_page'){
			$objectName = str_replace(' ', '', $jsonObject["object_name"]);
			$addEditContent = str_replace('{{objectName}}', $objectName, $addEditContent);
			$addEditContent = str_replace('{{objectName-lowercase}}', strtolower($objectName), $addEditContent);
		}else 
			$controllerContents = str_replace('{{addEditFunctions}}', "", $controllerContents);
		if($jsonObject["view_mode"] == 'different_page'){
			$objectName = str_replace(' ', '', $jsonObject["object_name"]);
			$viewContent = str_replace('{{objectName}}', $objectName, $viewContent);
			$viewContent = str_replace('{{objectName-lowercase}}', strtolower($objectName), $viewContent);
		}else
			$controllerContents = str_replace('{{viewFunction}}', "", $controllerContents);
		$controllerContents = str_replace('{{addEditFunctions}}', $addEditContent, $controllerContents);
		$controllerContents = str_replace('{{viewFunction}}', $viewContent, $controllerContents);

		return $controllerContents;
	}

}