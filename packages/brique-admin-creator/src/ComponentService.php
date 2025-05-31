<?php
namespace BriqueAdminCreator;
use Illuminate\Support\Facades\Storage;
use BriqueAdminCreator\CreatorUtils;
use Illuminate\Support\Facades\Log;

class ComponentService{

	public static function generateMainComponentContent($jsonObject, $allColumns){
		$componentContents = "";
		$componentContents = file_get_contents(__DIR__.'/storage/app/component/ComponentMain.txt');
		//Preparing for modal
		$componentAddEditFormContents = file_get_contents(__DIR__.'/storage/app/component/ComponentModal.txt');
		$componentViewRecordModalContents = file_get_contents(__DIR__.'/storage/app/component/ComponentModal.txt');
		// =========
		$modalSaveBtn = '<button type="button" class="btn btn-dark btn-sm" @click="save'.$jsonObject["object_name"].'()">SAVE CHANGES</button>';
		//For Add Modal
		$componentAddEditFormContents = str_replace('{{objectName}}', $jsonObject["object_name"], $componentAddEditFormContents);
		$componentAddEditFormContents = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $componentAddEditFormContents);
		$componentAddEditFormContents = str_replace('{{objectName-uppercase}}', $jsonObject["object_name"], $componentAddEditFormContents);
		$componentAddEditFormContents = str_replace('{{modalMode}}', 'addedit', $componentAddEditFormContents);
		$componentAddEditFormContents = str_replace('{{modalSaveBtn}}', $modalSaveBtn, $componentAddEditFormContents);
		$componentAddEditFormContents = str_replace('{{forAdd}}', ':'.strtolower($jsonObject["object_name"]).'ForAdd="'.strtolower($jsonObject["object_name"]).'ForAdd"', $componentAddEditFormContents);
		$componentAddEditFormContents = str_replace('{{forView}}', '', $componentAddEditFormContents);
		
		//For View Modal
		$componentViewRecordModalContents = str_replace('{{modalSaveBtn}}', '', $componentViewRecordModalContents);
		$componentViewRecordModalContents = str_replace('{{objectName}}', $jsonObject["object_name"], $componentViewRecordModalContents);
		$componentViewRecordModalContents = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $componentViewRecordModalContents);
		$componentViewRecordModalContents = str_replace('{{objectName-uppercase}}', $jsonObject["object_name"], $componentViewRecordModalContents);
		$componentViewRecordModalContents = str_replace('{{modalMode}}', 'view', $componentViewRecordModalContents);
		$componentViewRecordModalContents = str_replace('{{forAdd}}', '', $componentViewRecordModalContents);
		$componentViewRecordModalContents = str_replace('{{forView}}', ':read'.$jsonObject["object_name"].'= read'.$jsonObject["object_name"].'', $componentViewRecordModalContents);
		// ========= :readViewModal="readViewModal"
		$componentContents = str_replace('{{objectName}}', $jsonObject["object_name"], $componentContents);
		$componentContents = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $componentContents);
		$componentContents = str_replace('{{objectName-uppercase}}', strtoupper($jsonObject["object_name"]), $componentContents);
		$componentContents = str_replace('{{objectLabel}}', $jsonObject["object_label"], $componentContents);
		$componentContents = str_replace('{{objectLabel-uppercase}}', strtoupper($jsonObject["object_label"]), $componentContents);
		// ====For Form mode====
		$addRedirection ='<a id="add_' . strtolower($jsonObject["object_name"]) . '_btn" class="btn btn-warning border-dark btn-sm" :href="this.docRoot+\'/' . strtolower($jsonObject["object_name"]) . '/add\'" role="button">Add</a>';
		$addPopup = '<button type="button" class="btn btn-warning border-dark btn-sm" data-bs-toggle="modal" ref="addeditModal" data-bs-target="#add'.$jsonObject["object_name"].'Modal" @click="prepareAddModal">Add</button>';
		if($jsonObject["form_mode"] === 'different_page' ){
			$componentContents = str_replace('{{addEditModal}}', '', $componentContents);
			$componentContents = str_replace('{{addRedirection}}', $addRedirection, $componentContents);
		}
		else{
			$componentContents = str_replace('{{addEditModal}}', $componentAddEditFormContents, $componentContents);
			$componentContents = str_replace('{{addRedirection}}', $addPopup, $componentContents);
		}
		// ====For View mode====
		if($jsonObject["view_mode"] === 'different_page' )
			$componentContents = str_replace('{{viewRecordModal}}', '', $componentContents);
		else
			$componentContents = str_replace('{{viewRecordModal}}', $componentViewRecordModalContents, $componentContents);
		
		// ======= Relationships
		// masterVariables, loadMasters, reloadMasters
		$masterVariables = "";
		$loadMasters = "";
		// $reloadMasters = "";
		foreach($allColumns as $formRelationshipColumn){
			if( $formRelationshipColumn["form"]["type"] == "relation" ){
				$masterVariables .= "all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List: [],\n";
				$loadMasters .= "this.all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List"." = await this.loadMasterData(this.docRoot+'/".strtolower($formRelationshipColumn['relation']['related_model'])."',{});\n";
				// $reloadMasters .= "thisVar.all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List"." = await thisVar.loadMasterData(this.docRoot+'/".strtolower($formRelationshipColumn['relation']['related_model'])."',{});\n";
			}
			else if( $formRelationshipColumn["form"]["type"] == "textoptions" ){
				$loadMasters .= "this.all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List"." = [";
				if( is_array($formRelationshipColumn["options"]) && count($formRelationshipColumn["options"]) > 0 ){
					foreach ($formRelationshipColumn["options"] as $formColumn) {
						$loadMasters .= "{ id: '".$formColumn["key"]."', title: '".$formColumn["value"]."'}, ";
					}
				}
				$loadMasters .= "];\n";
			}
		}
		$componentContents = str_replace('{{masterVariables}}', $masterVariables, $componentContents);
		$componentContents = str_replace('{{loadMasters}}', $loadMasters, $componentContents);
		// $componentContents = str_replace('{{reloadMasters}}', $reloadMasters, $componentContents);

		// ===Table content for list field ===
		$tableContent = self::generateTableContent($jsonObject, $allColumns);
		$componentContents = str_replace('{{listFields}}', $tableContent["fieldsList"], $componentContents);
		$componentContents = str_replace('{{searchParams}}', $tableContent["advancedSearchContent"], $componentContents);
		
		// ========For Prepare Edit=============
		$forDifferentPage = "window.location = this.docRoot + '/".strtolower($jsonObject["object_name"])."/edit/' + ".strtolower($jsonObject["object_name"]).".id;";
		$forPopupModal = "this.".strtolower($jsonObject["object_name"])."ForAdd = Object.assign({}, ".strtolower($jsonObject["object_name"]).");\nthis.addeditModal.show();";
		$mountedAddEditModal = "this.addeditModal = new bootstrap.Modal(this.\$refs.addeditModal, { backdrop: 'static', keyboard: false });";
		$mountedReadModal = "this.viewModal = new bootstrap.Modal(this.\$refs.viewModal, { backdrop: 'static', keyboard: false });";
		if($jsonObject["form_mode"] == 'different_page'){
			$componentContents = str_replace("{{redirectionForPrepareEdit}}", $forDifferentPage, $componentContents);
			$componentContents = str_replace("{{mountedAddEditModal}}", "", $componentContents);
		}else{

			$componentContents = str_replace("{{redirectionForPrepareEdit}}", $forPopupModal, $componentContents);
			$componentContents = str_replace("{{mountedAddEditModal}}", $mountedAddEditModal, $componentContents);
		}
		// =====================
		// =========For View============
		$viewDifferentPage = "window.location = this.docRoot + '/".strtolower($jsonObject["object_name"])."/view/' + ".strtolower($jsonObject["object_name"]).".id;";
		$viewPopupModal = "this.read".$jsonObject["object_name"]." = Object.assign({}, ".strtolower($jsonObject["object_name"]).");\nthis.viewModal.show();"; 
		if($jsonObject["view_mode"] == 'different_page'){
			$componentContents = str_replace("{{redirectionForView}}", $viewDifferentPage, $componentContents);
			$componentContents = str_replace("{{mountedReadModal}}", "", $componentContents);
		}else{
			$componentContents = str_replace("{{redirectionForView}}", $viewPopupModal, $componentContents);
			$componentContents = str_replace("{{mountedReadModal}}", $mountedReadModal, $componentContents);
		}
		// =====================

		return $componentContents;
	}
	
	public static function generateComponentAddEditContent($jsonObject, $allColumns){
		$componentAddEditContents = file_get_contents(__DIR__.'/storage/app/component/ComponentAddEdit.txt');
		$uploadFileContent = file_get_contents(__DIR__.'/storage/app/component/ComponentDocument.txt');
		$templateSaveMethodContent = file_get_contents(__DIR__.'/storage/app/component/TemplateSaveMethod.txt');
		//Template for upload
		$templateAllUploadMethodContents = file_get_contents(__DIR__.'/storage/app/component/TemplateAllUploadMethod.txt');
		$templateAddModalWatchContents = file_get_contents(__DIR__.'/storage/app/component/TemplateAddModalWatch.txt');
		// $templateCancelUploadConditionMethodContents = file_get_contents(__DIR__.'/storage/app/component/TemplateCancelUploadConditionMethod.txt');
	
		$vueAddEditFieldsDefine = "";
		$vueAddEditFieldsValidation = "";
		$formRelationshipColumns = [];
		$fileUpload = [];
		$counter = 0;
		foreach($allColumns as $formColumn){
			if($formColumn["form"]["type"]== 'fileupload'){
				array_push($fileUpload, $formColumn);
			}
			$vueAddEditFieldsDefine .= $formColumn["name"].":";
			
			$validationClause = "{ ";
			if( $formColumn["form"]["required"] )
				$validationClause .= " required, ";
			if( $formColumn["form"]["type"] == "email" ){
				$validationClause .= "email";
				$vueAddEditFieldsDefine .= "''";
			}
			else if( $formColumn["form"]["type"] == "text" ){
				// $vueAddEditFieldsDefine .= "''";
				if(isset($formColumn["form"]["autogenerate"]) && $formColumn["form"]["autogenerate"])
					$vueAddEditFieldsDefine .= "'AUTOGENERATED'";	
				else
					$vueAddEditFieldsDefine .= "''";
				if ($formColumn["form"]["length_restriction"] && $formColumn["form"]["use_mode_length"] === 'fixed')
					$validationClause .= "minLength: minLength(".$formColumn["form"]["length"].")";
				else if ($formColumn["form"]["length_restriction"] && $formColumn["form"]["use_mode_length"] === 'range')
					$validationClause .= "minLength: minLength(".$formColumn["form"]["min_length"]."), maxLength: maxLength(".$formColumn["form"]["max_length"].")";
			}
			else if( $formColumn["form"]["type"] == "numeric" ){
				$vueAddEditFieldsDefine .= "0";
				if ($formColumn["form"]["value_restriction"] && $formColumn["form"]["use_min_value"] && $formColumn["form"]["use_max_value"]) {
					$validationClause .= "numeric, minValue: minValue(".$formColumn["form"]["min_value"]."), maxValue: maxValue(".$formColumn["form"]["max_value"].")";
				} else if ($formColumn["form"]["value_restriction"] && $formColumn["form"]["use_min_value"]) {
					$validationClause .= "numeric, minValue: minValue(".$formColumn["form"]["min_value"].")";
				} else if ($formColumn["form"]["value_restriction"] && $formColumn["form"]["use_max_value"]) {
					$validationClause .= "numeric, maxValue: maxValue(".$formColumn["form"]["max_value"].")";
				}
			}
			else if( $formColumn["form"]["type"] == "decimal" ){
				$vueAddEditFieldsDefine .= "0";
				$validationClause .= "decimal";
				if ($formColumn["form"]["value_restriction"] && $formColumn["form"]["use_min_value"] && $formColumn["form"]["use_max_value"])
					$validationClause .= ", minValue: minValue(".$formColumn["form"]["min_value"]."), maxValue: maxValue(".$formColumn["form"]["max_value"].")";
				else if ($formColumn["form"]["value_restriction"] && $formColumn["form"]["use_min_value"])
					$validationClause .= ", minValue: minValue(".$formColumn["form"]["min_value"].")";
				else if ($formColumn["form"]["value_restriction"] && $formColumn["form"]["use_max_value"]) 
					$validationClause .= ", maxValue: maxValue(".$formColumn["form"]["max_value"].")";
			}
			else if( $formColumn["form"]["type"] == "url" ){
				$validationClause .= "url";
				$vueAddEditFieldsDefine .= "''";
			}
			else if( $formColumn["form"]["type"] == "date" || $formColumn["form"]["type"] == "time" ){
				$vueAddEditFieldsDefine .= "''";
			}
			// else if( $formColumn["form"]["type"] == "select"  ){
			// 	$vueAddEditFieldsDefine .= "null";
			// 	if( array_search($formColumn["form"]["type"], ["relation", "textoptions"]) !== FALSE )
			// 		array_push($formRelationshipColumns, $formColumn);
			// }
			else if( $formColumn["form"]["type"] == "checkbox" ){
				$vueAddEditFieldsDefine .= "false";
			}
			else{
				$vueAddEditFieldsDefine .= "''";
			}
			$validationClause .= " }";
			$vueAddEditFieldsDefine .= ",".($counter < count($allColumns)-1 ? "\n":"");
			if(
				(in_array($formColumn["form"]["type"], ['numeric', 'decimal', 'email', 'url']) || $formColumn["form"]["value_restriction"] || 
				$formColumn["form"]["length_restriction"] || 
				$formColumn["form"]["required"] ) && trim($validationClause) != "{  }" 
			){
				$vueAddEditFieldsValidation .= $formColumn["name"] . ": " . $validationClause . ",\n";
			}
		}
		$forRichEditorData = "";
		$forRichEditorImport = "";
		$forRichEditorMethod = "initQuill(){\n";
		$forRichEditorMounted = "";
		$forRichEditorSave = "";
		$forDatePickerImport = "";
		$dateFieldFormat = "";
		$forMultiSelect = "";
		$dateFieldForMultiselect = "";
		$relationMultiselect ="";
		$textoptionMultiSelect="";
		$relationMultiselectReverse ="";
		$textOption = "";
		$textOptionReverse = "";
		$forValueRichEditor = "";
		$forWatchEditor ="this.\$nextTick(() => {";
		$forWatchElseEditor ="this.\$nextTick(() => {";
		$forWatchMonthPicker = "";
		$forWatchDate = "";
		$relationMultiselectForReload ="";
		$textOptionForReload="";
		$relationMounted = "";
		foreach($allColumns as $formColumn){
			if( $formColumn["form"]["type"] === 'richeditor'){
				$forRichEditorData .= "editor_" . $formColumn["name"] . " : null,\n";
				$forRichEditorImport = "import Quill from 'quill';\nimport 'quill/dist/quill.snow.css';";
				$forRichEditorMethod .= "this.editor_".$formColumn["name"]." = new Quill(this.\$refs.ref_".$formColumn["name"].", {\n\ttheme: 'snow',\n\tplaceholder: 'Enter description here...',\n\t});\n";
				$forRichEditorSave .= "that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." = that.editor_".$formColumn["name"].".root.innerHTML;\n";
				$forRichEditorMounted = "\nthis.initQuill();";
				$forValueRichEditor .="if(this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]."){\n\tthis.editor_".$formColumn["name"].".clipboard.dangerouslyPasteHTML(0, this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].");}\n";
				$forWatchEditor .= "\nif (this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].") {\nthis.editor_".$formColumn["name"].".clipboard.dangerouslyPasteHTML(0, this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].");\n}\n";
				$forWatchElseEditor .= "\nthis.editor_".$formColumn["name"].".setContents([]);\nthis.editor_".$formColumn["name"].".deleteText(0, this.editor_".$formColumn["name"].".getLength());\n";
			}
			if( $formColumn["form"]["type"] === 'date' && !in_array($formColumn["name"], ['id', 'created_at', 'updated_at'])){
				$forDatePickerImport = "import Datepicker from 'vue3-datepicker';";
				$dateFieldFormat .= "\nif (this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].") {
					this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." = this.formatDate(that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].");
				}\n";
				$dateFieldForMultiselect .= "that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." = new Date(that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].");\n";
				$forWatchDate .= "if (this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].") {\nthis.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." = new Date(this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].");\n}\n";
			}
			if( $formColumn["form"]["options_display_as"] === 'dropdown')
				$forMultiSelect = "displayLabelSetting ({id, text}) {\n\treturn `\${text}`;\n},";
			if( isset($formColumn["form"]["type"]) && $formColumn["form"]["type"] === 'relation'){
				$pascalCaseName = CreatorUtils::getPascalCase($formColumn["name"]);
				$objectNameLowercase = strtolower($jsonObject["object_name"]);
        		$relationMultiselect .= <<<Watch
if (this.{$objectNameLowercase}FormObj.{$formColumn["name"]}) {
    if (Array.isArray(this.all{$pascalCaseName}List) && this.all{$pascalCaseName}List.length > 0) {
        let _allRelationList = this.all{$pascalCaseName}List;
        let relationId = parseInt(this.{$objectNameLowercase}FormObj.{$formColumn["name"]});
        this.{$objectNameLowercase}FormObj.{$formColumn["name"]} = _allRelationList.find(item => item.id === relationId);
    }
}
Watch;
				//for reload that = this;
				$relationMultiselectForReload .="if ( that.".strtolower($jsonObject["object_name"])."FormObj?.".$formColumn["name"].") {\nthat.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." = that.all".CreatorUtils::getPascalCase($formColumn["name"])."List.find(item => item.id === that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].");\n}\n";

				$relationMultiselectReverse .="if (  typeof that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." === 'object' && that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].".id) {\nthat.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." = that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].".id;\n}\n";
			}
			if( isset($formColumn["form"]["type"]) && $formColumn["form"]["type"] === 'textoptions'){
				$textoptionMultiSelect .= "if (this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].") {\n
				if (Array.isArray(this.all".CreatorUtils::getPascalCase($formColumn["name"])."List) && this.all".CreatorUtils::getPascalCase($formColumn["name"])."List.length > 0) {\n
				const selectedId = this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].";
				this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." = this.all".CreatorUtils::getPascalCase($formColumn["name"])."List.find(item => item.id === selectedId);}\n}\n";
				$textOption .="\nif ( this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." && typeof this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." === 'string' ) {\nthis.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." = JSON.parse(this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].");\n}\n";

				//for reload that = this;
				$textOptionForReload .="\nif ( that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." && typeof that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." === 'string' ) {\nthat.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." = JSON.parse(that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].");\n}\n";

				$textOptionReverse .="\nif (  typeof that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." === 'object' &&  Object.keys(that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].").length > 0) {\nthat.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." = that.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].".id;\n}\n";
			}
			if(isset($formColumn["form"]["type"]) && $formColumn["form"]["type"] === 'monthpicker'){
				$forWatchMonthPicker .= "this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"]." = this.".strtolower($jsonObject["object_name"])."FormObj.".$formColumn["name"].";";
			}
		}
		$forWatchElseEditor .= "});\n";
		$forWatchEditor .= "});\n";
		$forRichEditorMethod .= "},\n";
		if($forRichEditorImport != null && $forRichEditorImport != ''){
			$componentAddEditContents = str_replace('{{forRichEditorMethod}}', $forRichEditorMethod, $componentAddEditContents);
		}else
			$componentAddEditContents = str_replace('{{forRichEditorMethod}}', '', $componentAddEditContents);
		$componentAddEditContents = str_replace('{{forRichEditorSave}}', $forRichEditorSave, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{forRichEditorImport}}', $forRichEditorImport, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{forRichEditorData}}', $forRichEditorData, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{forRichEditorMounted}}', $forRichEditorMounted, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{forDatePickerImport}}', $forDatePickerImport, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{forMultiSelect}}', $forMultiSelect, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{dateFieldFormat}}', $dateFieldFormat, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{dateFieldForMultiselect}}', $dateFieldForMultiselect, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{relationMultiselectReverse}}', $relationMultiselectReverse, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{textOption}}', $textOption, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{textOptionReverse}}', $textOptionReverse, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{forValueRichEditor}}', $forValueRichEditor, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{relationMultiselectForReload}}', $relationMultiselectForReload, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{textOptionForReload}}', $textOptionForReload, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{relationMounted}}', $relationMultiselect, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{textoptionMultiSelect}}', $textoptionMultiSelect, $componentAddEditContents);
		
		//template ADD Modal Watch
		$templateAddModalWatchContents = str_replace('{{textoptionMultiSelect}}', $textoptionMultiSelect, $templateAddModalWatchContents);
		$templateAddModalWatchContents = str_replace('{{relationMultiselect}}', $relationMultiselect, $templateAddModalWatchContents);
		$templateAddModalWatchContents = str_replace('{{textOption}}', $textOption, $templateAddModalWatchContents);
		$templateAddModalWatchContents = str_replace('{{forWatchDate}}', $forWatchDate, $templateAddModalWatchContents);
		$templateAddModalWatchContents = str_replace('{{forWatchEditor}}', $forValueRichEditor, $templateAddModalWatchContents);
		$templateAddModalWatchContents = str_replace('{{forWatchElseEditor}}', $forWatchElseEditor, $templateAddModalWatchContents);
		$templateAddModalWatchContents = str_replace('{{forWatchMonthPicker}}', $forWatchMonthPicker, $templateAddModalWatchContents);
		$templateAddModalWatchContents = str_replace('{{objectName}}', $jsonObject["object_name"], $templateAddModalWatchContents);
		$templateAddModalWatchContents = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $templateAddModalWatchContents);
		

		$componentAddEditContents = str_replace('{{vue-addEditFields}}', $vueAddEditFieldsDefine, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{vue-addEditFieldValidations}}', $vueAddEditFieldsValidation, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{objectName}}', $jsonObject["object_name"], $componentAddEditContents);
		$componentAddEditContents = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $componentAddEditContents);
		$componentAddEditContents = str_replace('{{objectLabel-uppercase}}', strtoupper($jsonObject["object_name"]), $componentAddEditContents);
		$componentAddEditContents = str_replace('{{objectLabel}}', $jsonObject["object_label"], $componentAddEditContents);
		// ======

		// masterVariables, loadMasters
		$masterVariables = "";
		$loadMasters = "";
		foreach($allColumns as $formRelationshipColumn){
			if( $formRelationshipColumn["form"]["type"] == "relation" ){
				$masterVariables .= "all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List: [],\n";
				// $loadMasters .= "this.all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List"." = await this.loadMasterData(this.docRoot+'/api/".strtolower($formRelationshipColumn['relation']['related_model'])."/get',{});\n";
				$loadMasters .="let _all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List = await this.loadMasterData(this.docRoot+'/api/".strtolower($formRelationshipColumn['relation']['related_model'])."/get', 'post', {});\nif(Array.isArray(_all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List) && _all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List.length > 0){\nthis.all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List = _all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List.map(x => {return { id: x.id, text: x.".$formRelationshipColumn["relation"]["title_attribute"]." }});\n}";
				// $reloadMasters .= "this.all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List"." = await thisVar.loadMasterData(docRoot+'/".strtolower($formRelationshipColumn['relation']['related_model'])."',{});\n";
			}
			else if( $formRelationshipColumn["form"]["type"] == "textoptions" ){
				$masterVariables .= "all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List: [],\n";
				$loadMasters .= "this.all".CreatorUtils::getPascalCase($formRelationshipColumn["name"])."List"." = [";
				if( is_array($formRelationshipColumn["options"]) && count($formRelationshipColumn["options"]) > 0 ){
					foreach ($formRelationshipColumn["options"] as $formColumn) {
						$loadMasters .= "{ id: '".$formColumn["key"]."', text: '".$formColumn["value"]."'}, ";
					}
				}
				$loadMasters .= "];\n";
			}
			
		}
		$componentAddEditContents = str_replace('{{masterVariables}}', $masterVariables, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{loadMasters}}', $loadMasters, $componentAddEditContents);
		// ==========

		// ========For Cancel Add Edit=============
		$cancelDifferentPage = "window.location = this.docRoot + '/".strtolower($jsonObject["object_name"])."';";
		$cancelPopupModal = "this.v$.\$reset();";
		$modalHideOnSave = '$("#add'.$jsonObject["object_name"].'Modal").modal("hide");';
		$objectNameLowercase = strtolower($jsonObject["object_name"]);

		if($jsonObject["form_mode"] == 'different_page'){
			$componentAddEditContents = str_replace("{{redirectionForCancelAddEdit}}", $cancelDifferentPage, $componentAddEditContents);
			$componentAddEditContents = str_replace("{{modalHideOnSave}}", "", $componentAddEditContents);
			$componentAddEditContents = str_replace("{{propObject}}", strtolower($jsonObject["object_name"]).'ForAdd: initialState(),', $componentAddEditContents);
			$componentAddEditContents = str_replace("{{watchMethodContent}}", '', $componentAddEditContents);

		}else{
			$componentAddEditContents = str_replace("{{redirectionForCancelAddEdit}}", $cancelPopupModal, $componentAddEditContents);
			$componentAddEditContents = str_replace("{{modalHideOnSave}}", $modalHideOnSave, $componentAddEditContents);
			$componentAddEditContents = str_replace("{{propObject}}", '', $componentAddEditContents);
			$componentAddEditContents = str_replace("{{watchMethodContent}}", $templateAddModalWatchContents, $componentAddEditContents);
		}
		// =====================

		// Repeat content based on the count of $fileUpload array
		$repeatedContent = "";
		$repeatedSaveConditionContent= "";
		$uploadIndexCondition = "";
		$prepareUploadCondition = "";
		$prepareUploadCondition2 = "";
		$handleUploadCondition= "";
		$clearUploadCondition = "";
		$cancelUploadLoop ="";
		foreach ($fileUpload as $index => $fileColumn) {
			$uploadIndex = $index + 1;
			$repeatedContent .= str_replace(['{{uploadIndex}}', '{{formatType}}'], [$uploadIndex, $fileColumn["form"]["format_type"]], $uploadFileContent) . "\n";
			// $repeatedContent .= str_replace('{{formatType}}', $fileColumn["form"]["format_type"], $uploadFileContent) . "\n";
			$objectNameLowercase = strtolower($jsonObject["object_name"]);
			if($uploadIndex > 1){
				//====== Save ======
				$repeatedSaveConditionContent .= "&& (that.document".$uploadIndex.".uploaded_file == null || that.document".$uploadIndex.".uploaded_file == undefined)";
				//====== cancelUpload ======
				$uploadIndexCondition .=<<<Cancel
else if (which == {$uploadIndex}){
	this.document{$uploadIndex} = {
		uploaded_file: null,
		contents: null,
		file_name: ""
	};
	this.\$refs.{$fileColumn["name"]}.value = null;
}				
Cancel;
				//====== prepareUpload ======
				$prepareUploadCondition .=<<<Prepare
else if (which == {$uploadIndex}) {
    this.document{$uploadIndex}.uploaded_file = file;
    this.document{$uploadIndex}.file_name = this.document{$uploadIndex}.uploaded_file.name;
}
Prepare;		
				$prepareUploadCondition2 .="\nelse if (which == ".$uploadIndex."){\n\tthis.document".$uploadIndex.".contents = e.target.result;}";
				//====== Handle Upload ======
				$handleUploadCondition .="\nif (this.document".$uploadIndex.".uploaded_file){\n\tformData.append('uploaded_file_".$uploadIndex."', this.document".$uploadIndex.".uploaded_file);}";
				//====== Clear Upload ======
				$clearUploadCondition .= <<<CLEAR
else if (purpose == {$uploadIndex} ){
	that.document{$uploadIndex} = { 
		uploaded_file: null, 
		contents: null,
		file_name: "" 
	};
	that.{$objectNameLowercase}FormObj.{$fileColumn["name"]}= null;
}
CLEAR;
				$cancelUploadLoop .= "this.cancelUpload(".$uploadIndex.");\n";
			}
			else{
				//====== Save ======
				$repeatedSaveConditionContent .= "(that.document".$uploadIndex.".uploaded_file == null || that.document".$uploadIndex.".uploaded_file == undefined)";
				//====== cancelUpload ======
				$uploadIndexCondition .=<<<Cancel
				if (which == {$uploadIndex}){
					this.document{$uploadIndex} = {
						uploaded_file: null,
						contents: null,
						file_name: ""
					};
					this.\$refs.{$fileColumn["name"]}.value = null;
				}				
				Cancel;
				//====== prepareUpload ======
				$prepareUploadCondition .=<<<Prepare
if (which == {$uploadIndex}) {
    this.document{$uploadIndex}.uploaded_file = file;
    this.document{$uploadIndex}.file_name = this.document{$uploadIndex}.uploaded_file.name;
}
Prepare;
				$prepareUploadCondition2 .="if (which == ".$uploadIndex."){\n\tthis.document".$uploadIndex.".contents = e.target.result;}";
				//====== Handle Upload ======
				$handleUploadCondition .="if (this.document".$uploadIndex.".uploaded_file){\n\tformData.append('uploaded_file_".$uploadIndex."', this.document".$uploadIndex.".uploaded_file);}";
				//====== Clear Upload ======
				$clearUploadCondition .= <<<CLEAR
if (purpose == {$uploadIndex} ){
	that.document{$uploadIndex} = { 
		uploaded_file: null, 
		contents: null,
		file_name: "" 
	};
	that.{$objectNameLowercase}FormObj.{$fileColumn["name"]}= null;
}
CLEAR;
			$cancelUploadLoop .= "this.cancelUpload(".$uploadIndex.");\n";
			}
			
			$templateSaveMethodContent = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $templateSaveMethodContent);
		}
		// $templateCancelUploadConditionMethodContents = $uploadIndexCondition;
		//save Method
		$uploadFileContent = $repeatedContent;
		$componentAddEditContents = str_replace('{{fileUploadArray}}', $repeatedContent, $componentAddEditContents);
		$templateSaveMethodContent = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $templateSaveMethodContent);
		$templateSaveMethodContent = str_replace('{{objectLabel}}', $jsonObject["object_label"], $templateSaveMethodContent);
		$templateSaveMethodContent = str_replace('{{saveCondition}}', $repeatedSaveConditionContent, $templateSaveMethodContent);
		//============== cancelUpload Method ==============
		$templateAllUploadMethodContents = str_replace('{{cancelUploadCondition}}', $uploadIndexCondition, $templateAllUploadMethodContents);
		//======== PrepareUpload =======
		$templateAllUploadMethodContents = str_replace('{{prepareUploadCondition}}', $prepareUploadCondition, $templateAllUploadMethodContents);
		$templateAllUploadMethodContents = str_replace('{{prepareUploadCondition2}}', $prepareUploadCondition2, $templateAllUploadMethodContents);
		//======== Handle Upload ======= 
		$templateAllUploadMethodContents = str_replace('{{handleUploadCondition}}', $handleUploadCondition, $templateAllUploadMethodContents);
		//======== Handle Upload ======= 
		$templateAllUploadMethodContents = str_replace('{{clearUploadCondition}}', $clearUploadCondition, $templateAllUploadMethodContents);

		// Set the final content
		$templateAllUploadMethodContents = str_replace('{{objectName}}', $jsonObject["object_name"], $templateAllUploadMethodContents);
		$templateAllUploadMethodContents = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $templateAllUploadMethodContents);
		if(count($fileUpload) > 0 ){
			$componentAddEditContents = str_replace('{{templateForSave}}', $templateSaveMethodContent, $componentAddEditContents);
			$componentAddEditContents = str_replace('{{templateForAllUpload}}', $templateAllUploadMethodContents, $componentAddEditContents);
			$componentAddEditContents = str_replace('{{cancelUploadLoop}}', $cancelUploadLoop, $componentAddEditContents);
		}else{
			$objectNameLowercase = strtolower($jsonObject["object_name"]);
			$saveMethodContent =<<<SAVE
that.showToast('{$jsonObject["object_label"]} saved successfully', 'success', 'bottom', 3000);
setTimeout(() => {
	window.location = that.docRoot + "/{$objectNameLowercase}/";
	that.showLoading("Loading ...");
}, 1500);
SAVE;
			$componentAddEditContents = str_replace('{{templateForSave}}', $saveMethodContent, $componentAddEditContents);
			$componentAddEditContents = str_replace('{{templateForAllUpload}}', '', $componentAddEditContents);
			$componentAddEditContents = str_replace('{{cancelUploadLoop}}', '', $componentAddEditContents);
		}

		//Fields generation
		$componentRichEditorFormField = file_get_contents(__DIR__.'/storage/app/component/fieldtypes/ComponentRichEditorFormField.txt');
		$componentInputFormField = file_get_contents(__DIR__.'/storage/app/component/fieldtypes/ComponentInputFormField.txt');
		$componentFileFormField = file_get_contents(__DIR__.'/storage/app/component/fieldtypes/ComponentFileFormField.txt');
		$componentRadioFormField = file_get_contents(__DIR__.'/storage/app/component/fieldtypes/ComponentRadioFormField.txt');
		$componentCheckboxFormField = file_get_contents(__DIR__.'/storage/app/component/fieldtypes/ComponentCheckboxFormField.txt');
		$componentTextAreaFormField = file_get_contents(__DIR__.'/storage/app/component/fieldtypes/ComponentTextAreaFormField.txt');
		$componentRelationFieldContents = file_get_contents(__DIR__.'/storage/app/component/fieldtypes/ComponentRelationFormField.txt');
		$componentMonthPickerFormContents = file_get_contents(__DIR__.'/storage/app/component/fieldtypes/ComponentMonthPickerFormField.txt');
		$componentDateFormContents = file_get_contents(__DIR__.'/storage/app/component/fieldtypes/ComponentDateFormField.txt');

		$fieldHelp = '<a href="#" class="cstooltip" data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote." tabindex="-1"><i class="ph ph-question"></i></a>';
		$allAddEditColumns = "";
		$fillableColumns=[];
		$lastIndex = count($jsonObject['columns']) - 1;
		$index = 0;
		$columnNameMultiselect="";
		$columnNameTextOptions="";
		foreach($jsonObject['columns'] as $column){
			if (isset($column['name']) && in_array($column['name'], ['id', 'updated_at', 'created_at', 'deleted_at'])) {
				continue;
			}
			array_push($fillableColumns, $column);
		}
		if(!empty($fillableColumns) && end($fillableColumns)['type'] === 'divider')
			array_pop($fillableColumns);

		// +++++ Add/Edit Fields Code +++++
		$allAddEditColumns .= "<div class='row mb-4'>\n";
		function formatDate($dateString) {
			$date = date_create($dateString);
			return date_format($date, "Y, n, j");
		}
		for ($i=0; $i < count($fillableColumns); $i++) {
			$fillableColumn = $fillableColumns[$i];
			if(isset($fillableColumn["use_in_form"]) && $fillableColumn["use_in_form"]){
				// if ($fillableColumn["form"]["type"] === 'fileUpload')
				switch( $fillableColumn["form"]["type"] ){
					//correct case select
					case 'relation':
						$allAddEditColumns .= "".$componentRelationFieldContents;
						$allAddEditColumns = str_replace('{{columnHelp}}', "", $allAddEditColumns);
						$allAddEditColumns = str_replace('{{defaultOption}}', "", $allAddEditColumns);
						$columnNameMultiselect .= "\n".$fillableColumn["name"]."Multiselect : null,\n";
						break;
					
					case 'richeditor':
						$allAddEditColumns .= "".$componentRichEditorFormField;
						$allAddEditColumns = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $allAddEditColumns);
						$allAddEditColumns = str_replace('{{columnName}}', $fillableColumn["name"], $allAddEditColumns);
						$allAddEditColumns = str_replace('{{columnHelp}}', '', $allAddEditColumns);
						break;

					case 'textarea':
						$allAddEditColumns .= "".$componentTextAreaFormField;
						$allAddEditColumns = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $allAddEditColumns);
						$allAddEditColumns = str_replace('{{columnName}}', $fillableColumn["name"], $allAddEditColumns);
						$allAddEditColumns = str_replace('{{columnHelp}}', $fieldHelp, $allAddEditColumns);
						$allAddEditColumns = str_replace('{{no_of_rows}}', $fillableColumn["form"]["no_of_rows"], $allAddEditColumns);
						break;
	
					case 'fileupload':
						$index++;
						$allAddEditColumns .= "".$componentFileFormField;
						$allAddEditColumns = str_replace('{{index}}', $index, $allAddEditColumns);
						$allAddEditColumns = str_replace('{{columnHelp}}', "", $allAddEditColumns);
						$allAddEditColumns = str_replace('{{formFieldClass}}', "", $allAddEditColumns);
						$allAddEditColumns = str_replace('{{maxFileSize}}', $fillableColumn["form"]["max_file_size"], $allAddEditColumns);
						$allAddEditColumns = str_replace('{{fileSizeType}}', $fillableColumn["form"]["file_size_type"], $allAddEditColumns);
						$allAddEditColumns = str_replace('{{formatType}}', $fillableColumn["form"]["format_type"], $allAddEditColumns);
						// $allAddEditColumns = str_replace('{{columnHelp}}', $fileHelp, $allAddEditColumns);
						break;
					case 'textoptions':
						$allAddEditColumns .= "".$componentRadioFormField;
						$textOptionsRadio = '';
						if($fillableColumn["form"]["options_display_as"] === 'dropdown'){
							$textOptionsRadio = "<multiselect v-model=\"".strtolower($jsonObject["object_name"])."FormObj.".$fillableColumn['name']."\" :options=\"all" . CreatorUtils::getPascalCase($fillableColumn['name']) . "List\" :custom-label=\"displayLabelSetting\" placeholder=\"Select one\"></multiselect>";
							$columnNameTextOptions .= "\n".$fillableColumn["name"]."TextOption : null,\n";

						}else if($fillableColumn["form"]["options_display_as"] === 'radiobtns'){
							foreach($fillableColumn["options"] as $frmOption){
							$textOptionsRadio .= <<<TEXT
<div class="form-check">
	<input class="form-check-input" type="radio" name="add_{$objectNameLowercase}_{$fillableColumn["name"]}" id="add_{$objectNameLowercase}_{$fillableColumn["name"]}_{$frmOption["key"]}" v-model="{$objectNameLowercase}FormObj.{$fillableColumn["name"]}" value="{$frmOption["key"]}">
	<label class="form-check-label" for="add_{$objectNameLowercase}_{$fillableColumn["name"]}_{$frmOption["key"]}">{$frmOption["value"]}</label>
</div>
TEXT;
							}
						}
						$allAddEditColumns = str_replace('{{textOptionsRadio}}', $textOptionsRadio, $allAddEditColumns);
						$allAddEditColumns = str_replace('{{columnHelp}}', "", $allAddEditColumns);
						$allAddEditColumns = str_replace('{{formFieldClass}}', " class=\"d-flex flex-row gap-3\"", $allAddEditColumns);
						break;
	
					case 'checkbox':
						$allAddEditColumns .= "".$componentCheckboxFormField;
						$allAddEditColumns = str_replace('{{columnHelp}}', "", $allAddEditColumns);
						break;

					case 'monthpicker':
						$allAddEditColumns .= "".$componentMonthPickerFormContents;
						$minMonth = $fillableColumn["form"]["min_month"];
						$maxMonth = $fillableColumn["form"]["max_month"];
						$allAddEditColumns = str_replace('{{minMonth}}', formatDate($minMonth), $allAddEditColumns);
						$allAddEditColumns = str_replace('{{maxMonth}}', formatDate($maxMonth), $allAddEditColumns);
						break;

					case 'date':
						$allAddEditColumns .= "".$componentDateFormContents;
						$minDate = $fillableColumn["form"]["min_date"];
						$maxDate = $fillableColumn["form"]["max_date"];
						$allAddEditColumns = str_replace('{{minDate}}', formatDate($minDate), $allAddEditColumns);
						$allAddEditColumns = str_replace('{{maxDate}}', formatDate($maxDate), $allAddEditColumns);
						break;
	
					default:
						$allAddEditColumns .= "".$componentInputFormField;
						if( $fillableColumn["form"]["prefix"] != null){
							$allAddEditColumns = str_replace('{{prefix}}', '<span class="input-group-text">' . $fillableColumn["form"]["prefix"] . '</span>', $allAddEditColumns);
						}else{
							$allAddEditColumns = str_replace('{{prefix}}', "", $allAddEditColumns);
						}
						if( $fillableColumn["form"]["suffix"] != null){
							$allAddEditColumns = str_replace('{{suffix}}', '<span class="input-group-text">' . $fillableColumn["form"]["suffix"] . '</span>', $allAddEditColumns);
						}else{
							$allAddEditColumns = str_replace('{{suffix}}', "", $allAddEditColumns);
						}
						if( $fillableColumn["form"]["type"] == "text"){
							if(isset($fillableColumn["form"]["autogenerate"]) && $fillableColumn["form"]["autogenerate"] === true){
								$allAddEditColumns = str_replace('{{isDisabled}}', 'disabled', $allAddEditColumns);
								$allAddEditColumns = str_replace('{{columnHelp}}', '', $allAddEditColumns);
							}else{
								$allAddEditColumns = str_replace('{{isDisabled}}', '', $allAddEditColumns);
								$allAddEditColumns = str_replace('{{columnHelp}}', $fieldHelp, $allAddEditColumns);
							}
						}
						else
							$allAddEditColumns = str_replace('{{columnHelp}}', "", $allAddEditColumns);
						$allAddEditColumns = str_replace('{{formFieldClass}}', "", $allAddEditColumns);
						break;
					
				}
				// $allAddEditColumns = str_replace('{{fieldWidth}}', $fieldSize, $allAddEditColumns);
				$allAddEditColumns = str_replace('{{columnName}}', $fillableColumn["name"], $allAddEditColumns);
				$allAddEditColumns = str_replace('{{columnHint}}', $fillableColumn["form"]["hint"], $allAddEditColumns);
				$allAddEditColumns = str_replace('{{columnLabel}}', $fillableColumn["form"]["label"], $allAddEditColumns);
				$allAddEditColumns = str_replace('{{objectName}}', $jsonObject["object_name"], $allAddEditColumns);
				$allAddEditColumns = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $allAddEditColumns);
				// relation related - fixed pending
				if( $fillableColumn["form"]["type"] == "relation" ){
					$allAddEditColumns = str_replace('{{relation}}', $fillableColumn["relation"]["related_model"], $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relation-camelcase}}', CreatorUtils::getCamelCase($fillableColumn["name"]), $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relation-pascalcase}}', CreatorUtils::getPascalCase($fillableColumn["name"]), $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relationTitle}}', $fillableColumn["relation"]["title_attribute"], $allAddEditColumns);
				}
				else if( $fillableColumn["form"]["type"] == "textoptions" ){
					$allAddEditColumns = str_replace('{{relation}}', $fillableColumn["name"], $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relation-camelcase}}', CreatorUtils::getCamelCase($fillableColumn["name"]), $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relation-pascalcase}}', CreatorUtils::getPascalCase($fillableColumn["name"]), $allAddEditColumns);
					$allAddEditColumns = str_replace('{{relationTitle}}', "title", $allAddEditColumns);
				}
				else{
					$vEmptyZero = "";
					switch ($fillableColumn["form"]["type"]) {
						case 'email':
							$allAddEditColumns = str_replace('{{columnType}}', "email", $allAddEditColumns);
							break;
						
						case 'numeric':
						case 'decimal':
							$allAddEditColumns = str_replace('{{columnType}}', "number", $allAddEditColumns);//v-empty-zero
							$vEmptyZero = "v-empty-zero";
							break;
							
						case 'date':
							$allAddEditColumns = str_replace('{{columnType}}', "date", $allAddEditColumns);
							break;

						case 'time':
							$allAddEditColumns = str_replace('{{columnType}}', "time", $allAddEditColumns);
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

						case 'fileupload':
							$allAddEditColumns = str_replace('{{columnType}}', "file", $allAddEditColumns);
							break;

						default:
							$allAddEditColumns = str_replace('{{columnType}}', "text", $allAddEditColumns);
							break;
					}
					$allAddEditColumns = str_replace('{{v-empty-zero}}', $vEmptyZero, $allAddEditColumns);
				}
				$required = "";
				$requiredError = "";
				if( $fillableColumn["form"]['required'] ){
					$required = ' <span class="mandatory">*</span>';
					$requiredError .= ' <div v-if="v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.$error && v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.required.$invalid" class="text-danger small">{{ v$.'.strtolower($jsonObject["object_name"]).'FormObj.text.required.$message }}</div>'."\n";
				}
				if( $fillableColumn["form"]['length_restriction'] && $fillableColumn["form"]["use_mode_length"] === 'fixed' && $fillableColumn["form"]["length"] >= 0){
					$requiredError .= ' <div v-if="v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.$error && v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.minLength.$invalid" class="text-danger small">{{ v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.minLength.$message }}</div>';
				}else if($fillableColumn["form"]['length_restriction'] && $fillableColumn["form"]["use_mode_length"] === 'range'){
					if( $fillableColumn["form"]["min_length"] >= 0 ){
						$requiredError .= ' <div v-if="v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.$error && v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.minLength.$invalid" class="text-danger small">{{ v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.minLength.$message }}</div>'."\n";
					}
					if( $fillableColumn["form"]["max_length"] >= 0 ){
						$requiredError .= ' <div v-if="v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.$error && v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.maxLength.$invalid" class="text-danger small">{{ v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.maxLength.$message }}</div>'."\n";
					}
				}
				if( $fillableColumn["form"]['value_restriction'] ){
					if( $fillableColumn["form"]["use_min_value"] && $fillableColumn["form"]["min_value"] >= 0 ){
						$requiredError .= ' <div v-if="v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.$error && v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.minValue.$invalid" class="text-danger small">{{ v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.minValue.$message }}</div>'."\n";
					}
					if( $fillableColumn["form"]["use_max_value"] && $fillableColumn["form"]["max_value"] >= 0 ){
						$requiredError .= ' <div v-if="v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.$error && v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.maxValue.$invalid" class="text-danger small">{{ v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.maxValue.$message }}</div>'."\n";
					}
				}
				if( $fillableColumn["form"]['type'] === 'email' ){
					$requiredError .= ' <div v-if="v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.$error" class="text-danger small">{{ v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.email.$message }}</div>'."\n";
				}
				if( $fillableColumn["form"]['type'] === 'url' ){
					$requiredError .= ' <div v-if="v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.$error" class="text-danger small">{{ v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.url.$message }}</div>'."\n";
				}
				if( $fillableColumn["form"]['type'] === 'decimal' ){
					$requiredError .= ' <div v-if="v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.$error" class="text-danger small">{{ v$.'.strtolower($jsonObject["object_name"]).'FormObj.'.$fillableColumn["name"].'.decimal.$message }}</div>'."\n";
				}
				$allAddEditColumns = str_replace('{{columnRequired}}', $required, $allAddEditColumns);
				$allAddEditColumns = str_replace('{{columnRequiredError}}', $requiredError, $allAddEditColumns);
				if(isset($fillableColumn["form"]["autogenerate"]) && $fillableColumn["form"]["autogenerate"] === true){
					$allAddEditColumns = str_replace('{{isDisabled}}', 'disabled', $allAddEditColumns);
				}else{
					$allAddEditColumns = str_replace('{{isDisabled}}', '', $allAddEditColumns);
				}
			}else{
				$allAddEditColumns .= "\n</div>\n<div class='row mb-4'>\n";
			}
		}
		$allAddEditColumns .= "</div>\n";
		$componentAddEditContents = str_replace('{{formFields}}', $allAddEditColumns, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{columnNameMultiselect}}', $columnNameMultiselect, $componentAddEditContents);
		$componentAddEditContents = str_replace('{{columnNameTextOptions}}', $columnNameTextOptions, $componentAddEditContents);
		
		return $componentAddEditContents;
	}

	public static function generateComponentViewContent($jsonObject, $allColumns){
		$componentViewContents = file_get_contents(__DIR__.'/storage/app/component/ComponentView.txt');
		// $componentViewRecordContents = file_get_contents(__DIR__.'/storage/app/component/ComponentViewRecord.txt');
		$componentViewField = file_get_contents(__DIR__.'/storage/app/component/ComponentViewField.txt');
		// $componentRadioField = file_get_contents(__DIR__.'/storage/app/ComponentRadioField.txt');
		// // <span>{{ read{{objectName}}.{{fieldName}} }}</span>
		// // <span v-if="read{{objectName}}.{{fieldName}}=={{key}}">{{value}}</span> - table_type
		// // {{ read{{objectName}}.{{fieldDetails}} }}
		$componentViewBadge = file_get_contents(__DIR__.'/storage/app/component/ComponentViewBadge.txt');
		// $componentViewRecordContents='';
		$allViewColumns = "";
		$masterVariables = "";
		$fileUploads = [];
		$viewColumns=[];
		$methodView="";
		$loadMasters="";
		// $fileUploadFieldData = "<template v-if='read".$jsonObject["object_name"].".".$viewColumn["name"]." && read".$jsonObject["object_name"].".".$viewColumn["name"].".length > 5'>\n<img :src=\"docRoot + '/".strtolower($jsonObject["object_name"])."/view-file/' + ".strtolower($jsonObject["object_name"])."FormObj.id + '/".$viewColumn["name"]."/_s/123'\" style=\"max-width: 100px\" />\n</template>\n<span v-else><i>Not specified</i></span>\n";
		foreach($jsonObject['columns'] as $column){
			if( isset($column["form"]) && $column["form"]["type"] == "relation" )
				$masterVariables .= "all".CreatorUtils::getPascalCase($column["name"])."List: [],\n";
			if(isset($column["form"]) && $column["form"]["type"]== 'fileupload')
				array_push($fileUploads, $column);
			if( $column['type'] === 'divider' || (isset($column["table_view"]) && $column["table_view"]["use_in_view"] === true ))
				array_push($viewColumns, $column);
		}
		if(!empty($viewColumns)){
			while (end($viewColumns)['type'] === 'divider') {
				array_pop($viewColumns);
			}
		}
		log::info($viewColumns);
		// +++++ View Fields Code +++++
		$allViewColumns .= "<div class='row mb-4'>\n";
		for ($i=0; $i < count($viewColumns); $i++) {
			$viewColumn = $viewColumns[$i];
			if( isset($viewColumn["table_view"]) && $viewColumn["table_view"]["use_in_view"] ){
				$allViewColumns .= $componentViewField;
				if( $viewColumn["table_view"]["badge"] == true ){
					$allViewColumns = str_replace('{{fieldData}}', $componentViewBadge, $allViewColumns);
				}
				else{
					$allOptionsContent = "";
					if( $viewColumn["table_view"]["type"] == "textoptions" ){
						foreach($viewColumn["options"] as $viewColumnOption){
							$allOptionsContent .= '<span v-if="read{{objectName}}.'.$viewColumn["name"].'==\''.$viewColumnOption["key"].'\'">'.$viewColumnOption["value"]."</span>\n";
						}
						$methodView .=<<<parse
							\nparsed{$viewColumn["name"]}() {
								try {
									if(this.read{$jsonObject["object_name"]} && this.read{$jsonObject["object_name"]}.{$viewColumn["name"]}){
										return JSON.parse(this.read{$jsonObject["object_name"]}.{$viewColumn["name"]});
									}
								} catch (e) {
									return {};
								}
							},\n
						parse;
						$loadMasters .= "this.all".CreatorUtils::getPascalCase($viewColumn["name"])."List"." = [";
							if( is_array($viewColumn["options"]) && count($viewColumn["options"]) > 0 ){
								foreach ($viewColumn["options"] as $formColumn) {
									$loadMasters .= "{ id: '".$formColumn["key"]."', text: '".$formColumn["value"]."'}, ";
								}
							}
							$loadMasters .= "];\n";
						$allViewColumns = str_replace('{{fieldData}}', $allOptionsContent, $allViewColumns);
					}
					else if( $viewColumn["table_view"]["type"] == "relation" ){
						$allViewColumns = str_replace('{{fieldData}}', "<span v-if='read{{objectName}}.".$viewColumn["relation"]["method"]."?.".$viewColumn["relation"]["title_attribute"]."'>{{ read{{objectName}}.".$viewColumn["relation"]["method"].".".$viewColumn["relation"]["title_attribute"]." }}</span><span v-else><i>Not specified</i></span>", $allViewColumns);
					}
					else if( $viewColumn["table_view"]["type"] == "date" ){ 
						$allViewColumns = str_replace('{{fieldData}}', "<span v-if='read{{objectName}}.".$viewColumn["name"]."'>{{ formatMySQLDate(read{{objectName}}.".$viewColumn["name"].", 'MMM dd, yyyy') }}</span><span v-else><i>Not specified</i></span>", $allViewColumns);
					}
					else if( $viewColumn["form"]["type"] == "monthpicker" ){ 
						$allViewColumns = str_replace('{{fieldData}}', "<span v-if='read{{objectName}}.".$viewColumn["name"]."'>{{ formatMySQLDate(read{{objectName}}.".$viewColumn["name"].", 'MMM, yyyy') }}</span><span v-else><i>Not specified</i></span>", $allViewColumns);
					}
					else if($viewColumn["form"]["type"] == "richeditor"){
						$allViewColumns = str_replace('{{fieldData}}', "<span v-if='read{{objectName}}.".$viewColumn["name"]."' v-html='read{{objectName}}.".$viewColumn["name"]."'></span><span v-else><i>Not specified</i></span>", $allViewColumns);
					}
					else if($viewColumn["form"]["type"] == "fileupload"){
						$allViewColumns = str_replace('{{fieldData}}', "<template v-if='read{{objectName}}.".$viewColumn["name"]." && read{{objectName}}.".$viewColumn["name"].".length > 5'>\n<img :src=\"docRoot + '/".strtolower($jsonObject["object_name"])."/view-file/' + read{{objectName}}.id + '/".$viewColumn["name"]."/_s/123'\" style=\"max-width: 100px\" />\n</template>\n<span v-else><i>Not specified</i></span>\n", $allViewColumns);
					}
					else if($viewColumn["form"]["type"] == "url"){
						$allViewColumns = str_replace('{{fieldData}}', "<a v-if='read{{objectName}}.".$viewColumn["name"]."' :href='read{{objectName}}.".$viewColumn["name"]."' target='_blank' class='text-primary'>{{ read{{objectName}}.".$viewColumn["name"]." }}</a><span v-else><i>Not specified</i></span>", $allViewColumns);
					}
					else
						$allViewColumns = str_replace('{{fieldData}}', "<span v-if='read{{objectName}}.".$viewColumn["name"]."'>{{ read{{objectName}}.".$viewColumn["name"]." }}</span><span v-else><i>Not specified</i></span>", $allViewColumns);
				}
				// $allViewColumns = str_replace('{{fieldWidth}}', $fieldSize, $allViewColumns);
				$allViewColumns = str_replace('{{fieldName}}', $viewColumn["name"], $allViewColumns);
				$allViewColumns = str_replace('{{fieldLabel}}', $viewColumn["table_view"]["label"], $allViewColumns);
				$allViewColumns = str_replace('{{objectName}}', $jsonObject["object_name"], $allViewColumns);
			}else{
				$allViewColumns .= "\n</div>\n<div class='row mb-4'>\n";
			}
		}
		$allViewColumns .= "\n</div>\n";
		$componentViewContents = str_replace('{{viewFields}}', $allViewColumns, $componentViewContents);

		$cancelUploadLoop = '';
		foreach ($fileUploads as $index => $column) {
			$uploadIndex = $index + 1;
			if($uploadIndex > 1)
				$cancelUploadLoop .= "this.cancelUpload(".$uploadIndex.");\n";
			else
				$cancelUploadLoop .= "this.cancelUpload(".$uploadIndex.");\n";
		}
		// ======== For Cancel Add Edit =============
		$cancelDifferentPage = "window.location = this.docRoot + '/".strtolower($jsonObject["object_name"])."';";
		$cancelPopupModal = "this.".strtolower($jsonObject["object_name"])."FormObj = initialState();\nthis.v$.\$reset();";
		if($jsonObject["form_mode"] == 'different_page'){
			$componentViewContents = str_replace("{{redirectionForCancelAddEdit}}", $cancelDifferentPage, $componentViewContents);
		}
		else{
			$componentViewContents = str_replace("{{redirectionForCancelAddEdit}}", $cancelPopupModal, $componentViewContents);
		}
		if($jsonObject["view_mode"] == 'different_page')
			$componentViewContents = str_replace("{{propObject}}", 'read'.$jsonObject["object_name"].': {},', $componentViewContents);
		else
			$componentViewContents = str_replace("{{propObject}}", '', $componentViewContents);

		if(count($fileUploads) > 0 )
			$componentViewContents = str_replace('{{cancelUploadLoop}}', $cancelUploadLoop, $componentViewContents);
		else
			$componentViewContents = str_replace('{{cancelUploadLoop}}', '', $componentViewContents);
		$componentViewContents = str_replace('{{masterVariables}}', $masterVariables, $componentViewContents);
		$componentViewContents = str_replace('{{objectName}}', $jsonObject["object_name"], $componentViewContents);
		$componentViewContents = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $componentViewContents);
		$componentViewContents = str_replace('{{objectLabel-uppercase}}', strtoupper($jsonObject["object_name"]), $componentViewContents);
		$componentViewContents = str_replace('{{methodView}}', $methodView, $componentViewContents);
		$componentViewContents = str_replace('{{loadMasters}}', $loadMasters, $componentViewContents);

		return $componentViewContents;
	}
	
	public static function generateTableContent($jsonObject, $allColumns){
		$listFields = "";
		foreach ($allColumns as $tableColumn) {
			if( $tableColumn["table_view"]["visible_in_table"] == false )
				continue;	
			$listFields .= "{ title: '" . $tableColumn["table_view"]["label"] . "', ";
			switch($tableColumn["table_view"]["type"]){
				case "relation":
					$listFields .= "property: '" . $tableColumn["relation"]["method"] . ".".$tableColumn["relation"]["title_attribute"]."', alt_value: '".$tableColumn["table_view"]['text_if_empty']."', ";
					break;
					
				case "textoptions":
					$listFields .= "property: '" . $tableColumn["name"] . "', ";
					$sourceEnum = "enum: { ";
					foreach($tableColumn["options"] as $option){
						$sourceEnum .= "'".$option["key"]."': '".$option["value"]."', ";
					}
					$sourceEnum .= " }, ";
					$listFields .= $sourceEnum;
					break;
					
				case "date":
					$listFields .= "property: '" . $tableColumn["name"] . "', date_type: 'mysqldate', display_type: 'date', format: 'LLL dd, yyyy', ";
					break;
				case "time":
					$listFields .= ""; 
				case "text":
					$listFields .= "property: '" . $tableColumn["name"] . "', ";
					break;
			}
			if ($tableColumn["table_view"]["sortable"])
				$listFields .= "sortable: true, ";
			if ($tableColumn["form"]["suffix"] && strlen($tableColumn["form"]["suffix"]) > 0)
				$listFields .= "suffix: ' " . $tableColumn["form"]["suffix"] . "', ";
			$listFields .= "},\n";
		}
		$advancedSearchContent = "";
		$advancedSearchContent = file_get_contents(__DIR__.'/storage/app/component/ComponentAdvSearch.txt');
		$advancedSearchColumnContent = file_get_contents(__DIR__.'/storage/app/component/ComponentAdvSearchColumn.txt');
		$allSearchableColumns = "";
		foreach ($allColumns as $tableColumn) {
			if( $tableColumn["table_view"]["searchable"] || $tableColumn["table_view"]["type"] == 'relation' || $tableColumn["table_view"]["type"] == 'textoptions' ){
				$allSearchableColumns .= $advancedSearchColumnContent;
				$allSearchableColumns = str_replace('{{columnLabel-uppercase}}', $tableColumn["table_view"]["label"], $allSearchableColumns);
				$allSearchableColumns = str_replace('{{columnName}}', $tableColumn["name"], $allSearchableColumns);
				$allSearchableColumns = str_replace('{{columnType}}', $tableColumn["table_view"]["type"], $allSearchableColumns);
				// Check the column type
				$masterDetails = "";
				$sourceEnum = "";
				if( $tableColumn["table_view"]["type"] === "textoptions" ){
					if( isset($tableColumn["options"]) && count($tableColumn["options"]) > 0 ){
						$sourceEnum = "source_enum: [ ";
						foreach($tableColumn["options"] as $option){
							$sourceEnum .= "{ id: '".$option["key"]."', value: '".$option["value"]."' }, ";
						}
						$sourceEnum .= " ]\n";
					}
					$allSearchableColumns = str_replace('{{sourceEnum}}', $sourceEnum, $allSearchableColumns);
				}else
					$allSearchableColumns = str_replace('{{sourceEnum}}', '', $allSearchableColumns);

				if( $tableColumn["table_view"]["type"] === "relation" ){
					if( isset($tableColumn["relation"]) ){
						$masterDetails = "source: { api: '".$tableColumn["relation"]["related_model"]."', id: '".$tableColumn["relation"]["related_model_id"]."', value: '".$tableColumn["relation"]["title_attribute"]."' } ";
					}
					$allSearchableColumns = str_replace('{{masterDetails}}', $masterDetails, $allSearchableColumns);
				}else
					$allSearchableColumns = str_replace('{{masterDetails}}', '', $allSearchableColumns);
			}

		}
		$advancedSearchContent = str_replace('{{searchArrayParams}}', $allSearchableColumns, $advancedSearchContent);
		return ["fieldsList" => $listFields, "advancedSearchContent" => $advancedSearchContent];
	}
}
