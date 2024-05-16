<?php
namespace BriqueAdminCreator;
use Illuminate\Support\Facades\Storage;
use BriqueAdminCreator\CreatorUtils;

class ComponentService{

	public static function generateComponentContent($jsonObject, $formColumns, $tableColumns, $viewColumns){
		$componentContents = "";
		if( $jsonObject["add_edit_mode"] == 1 ){
		}
		else if( $jsonObject["add_edit_mode"] == 2 ){
			// popup mode
			$componentContents = file_get_contents(__DIR__.'/storage/app/ComponentPopup.txt');
			$componentAddEditFormContents = self::generatePopupComponentAddEditContent($jsonObject, $formColumns);
			$componentViewRecordContents = self::generatePopupComponentViewContent($jsonObject, $viewColumns);
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
				$masterVariables .= "all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List: [],\n";
				if( $formRelationshipColumn["frm_select_type"] == "relation" ){
					$loadMasters .= "this.all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List"." = await this.loadAll".$formRelationshipColumn["frm_related_model"]."(true);\n";
					$reloadMasters .= "thisVar.all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List"." = await thisVar.loadAll".$formRelationshipColumn["frm_related_model"]."(true);\n";
				}
				else if( $formRelationshipColumn["frm_select_type"] == "fixed" ){
					$loadMasters .= "this.all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List"." = [";
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
			$tableContent = self::generateTableContent($jsonObject, $tableColumns, $jsonObject["search_type"]);
			$componentContents = str_replace('{{listFields}}', $tableContent["fieldsList"], $componentContents);
			$componentContents = str_replace('{{advancedSearchParams}}', $tableContent["advancedSearchContent"], $componentContents);
		}
		return $componentContents;
	}

	public static function generatePopupComponentAddEditContent($jsonObject, $fillableColumns){
		$componentAddEditFormContents = file_get_contents(__DIR__.'/storage/app/ComponentAddEdit.txt');
		$componentAddEditComponent = file_get_contents(__DIR__.'/storage/app/ComponentAddEditComponent.txt');
		$componentInputFormField = file_get_contents(__DIR__.'/storage/app/ComponentInputFormField.txt');
		$componentFileFormField = file_get_contents(__DIR__.'/storage/app/ComponentFileFormField.txt');
		$componentTextAreaFormField = file_get_contents(__DIR__.'/storage/app/ComponentTextAreaFormField.txt');
		$componentRadioFormField = file_get_contents(__DIR__.'/storage/app/ComponentRadioFormField.txt');
		$componentCheckboxFormField = file_get_contents(__DIR__.'/storage/app/ComponentCheckboxFormField.txt');
		$componentRelationFieldContents = file_get_contents(__DIR__.'/storage/app/ComponentRelationFormField.txt');
	
		$fieldHelp = '<a href="#" class="cstooltip" data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote." tabindex="-1"><i class="ph ph-question"></i></a>';
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
					$allAddEditColumns = str_replace('{{relation-camelcase}}', CreatorUtils::getCamelCase($fillableColumn["name"]), $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relation-pascalcase}}', CreatorUtils::getPascalCase($fillableColumn["name"]), $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relationTitle}}', $fillableColumn["related_to_model_title"], $allAddEditColumns);
				}
				else if( $fillableColumn["frm_select_type"] == "fixed" ){
					$allAddEditColumns = str_replace('{{relation}}', $fillableColumn["name"], $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relation-camelcase}}', CreatorUtils::getCamelCase($fillableColumn["name"]), $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relation-pascalcase}}', CreatorUtils::getPascalCase($fillableColumn["name"]), $allAddEditColumns);
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

	public static function generatePopupComponentViewContent($jsonObject, $viewColumns){
		$componentViewRecordContents = file_get_contents(__DIR__.'/storage/app/ComponentViewRecord.txt');
		$componentViewField = file_get_contents(__DIR__.'/storage/app/ComponentViewField.txt');
		// $componentRadioField = file_get_contents(__DIR__.'/storage/app/ComponentRadioField.txt');
		$componentViewBadge = file_get_contents(__DIR__.'/storage/app/ComponentViewBadge.txt');
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

	
	public static function generateTableContent($jsonObject, $tableColumns, $searchType){
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
			$advancedSearchContent = file_get_contents(__DIR__.'/storage/app/ComponentAdvSearch.txt');
			$advancedSearchColumnContent = file_get_contents(__DIR__.'/storage/app/ComponentAdvSearchColumn.txt');
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
}