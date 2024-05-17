<?php
namespace BriqueAdminCreator;

use BriqueAdminCreator\Models\GeneratedModule;
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
			if( !in_array($tableValues[0], ["failed_jobs", "migrations", "password_reset_tokens", "password_resets", "personal_access_tokens"]) )
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

	// Main Function
	public function getGeneratedCode(Request $request){
		$jsonObject = $request->all();
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
		$modelContents = ModelService::generateModelContent($jsonObject, $fillableColumns, $tableRelationshipColumns);
		// =====================
		$controllerContents = ModelService::generateControllerContent($jsonObject, $searchColumns, $requiredColumns, $uniqueColumn, $tableRelationshipColumns);
		// =====================
		$componentContents = ComponentService::generateComponentContent($jsonObject, $formColumns, $tableColumns, $viewColumns);
		if( $uniqueColumn != null && !empty($uniqueColumn) ){
			$componentContents = str_replace('{{uniqueColumn}}', $uniqueColumn["name"], $componentContents);
			$componentContents = str_replace('{{uniqueColumnLabel}}', $uniqueColumn["frm_label"], $componentContents);
		}
		// =====================
		$exportContents = ModelService::generateExportContent($jsonObject, $requiredColumns);
		// =====================
		$readmeContentsArray = ModelService::generateReadmeContent($jsonObject, $formRelationshipColumns);
		$readmeContents = $readmeContentsArray["readme"];
		$componentMixinContents = $readmeContentsArray["mixin"];
		// =====================

		// =====================
		// Install Model in the folder
		file_put_contents(base_path().DIRECTORY_SEPARATOR.
			'app'.DIRECTORY_SEPARATOR.'Models'.DIRECTORY_SEPARATOR.
			$jsonObject["object_name"].'.php', $modelContents, LOCK_EX);
		// Controller
		file_put_contents(base_path().DIRECTORY_SEPARATOR.
			'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.
			$jsonObject["object_name"].'Controller.php', $controllerContents, LOCK_EX);
		// Export
		file_put_contents(base_path().DIRECTORY_SEPARATOR.
			'app'.DIRECTORY_SEPARATOR.'Exports'.DIRECTORY_SEPARATOR.
			$jsonObject["object_name"].'Export.php', $exportContents, LOCK_EX);

		if( count($formRelationshipColumns) > 0 ){
			// Mixins
			$mixinRelations = $readmeContentsArray["mixin_relations"];
			// Check if the masters.js file exists.
			$masterJsPath = base_path().DIRECTORY_SEPARATOR.
				'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'mixins'.DIRECTORY_SEPARATOR.'masters.js';
			if( file_exists($masterJsPath)){
				// If so, read the masters.js contents.
				$masterJsContents = file_get_contents($masterJsPath);
				// $masterJsContents = preg_replace('/\s+/', '', $masterJsContents);
				$offset = -1;
				if( strpos($masterJsContents, "methods:") > 0 ){
					$offset = strpos($masterJsContents, "methods:") + 8;
					$offset = strpos($masterJsContents, "{", $offset) + 1;
				}
				if( $offset > 0 ){
					// Search for individual files
					foreach( $mixinRelations as $mixin => $mixinMethod ){
						if( !strpos($masterJsContents, $mixin) > 0 ){
							// This means it doesnt exist
							$masterJsContents = substr_replace($masterJsContents, "\n".$mixinMethod."\n", $offset, 0);
							$offset += strlen($mixinMethod) + 2;
						}
					}
				}
				file_put_contents(base_path().DIRECTORY_SEPARATOR.
					'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'mixins'.DIRECTORY_SEPARATOR.
					'masters.js', $masterJsContents, LOCK_EX);
			}
			else{
				// Since the file doesnt exist
				file_put_contents(base_path().DIRECTORY_SEPARATOR.
					'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'mixins'.DIRECTORY_SEPARATOR.
					'masters.js', $componentMixinContents, LOCK_EX);
			}
		}
		// Menu Item
		// 1. Load the LeftNavbarComponent. It must exist
		$leftNavbarComponentPath = base_path().DIRECTORY_SEPARATOR.
			'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'LeftNavbarComponent.vue';
		if( file_exists($leftNavbarComponentPath) ){
			$leftNavbarComponentContents = file_get_contents($leftNavbarComponentPath);
			$_templateStart = strpos($leftNavbarComponentContents, "<template>");
			$_templateEnd = strrpos($leftNavbarComponentContents, "</template>");
			$leftNavbarComponentDocContents = substr($leftNavbarComponentContents, $_templateStart, ($_templateEnd-$_templateStart+11));
			$doc = new \DOMDocument();
			$doc->preserveWhiteSpace = false;
			$doc->formatOutput = true;
			// Suppress errors caused by malformed HTML
			libxml_use_internal_errors(true);
			$doc->loadXML(trim($leftNavbarComponentDocContents));
			$xpath = new \DOMXPath($doc);
			$parentNodes = $xpath->query("//*[@id='".$jsonObject["navigation_group"]."']");
			if ($parentNodes->length > 0) {
				$parentNode = $parentNodes->item(0);
				$menuItem = <<<MENU
<li class="nav-item"><a class="nav-link p-0 pt-2 d-flex align-items-center" :class="{ 'active': currentRoute === '{{objectName-lowercase}}' }" href="/{{objectName-lowercase}}"><i class="ph-camera me-2"></i><span>{{objectLabel}}</span></a></li>
MENU;
				$menuItem = str_replace('{{objectName}}', $jsonObject["object_name"], $menuItem);
				$menuItem = str_replace('{{objectName-lowercase}}', strtolower($jsonObject["object_name"]), $menuItem);
				$menuItem = str_replace('{{objectLabel}}', $jsonObject["object_label"], $menuItem);
				if (!empty($menuItem) && simplexml_load_string($menuItem)) {
					$fragment = $doc->createDocumentFragment();
					if( $fragment->appendXML($menuItem) )
						$parentNode->appendChild($fragment);
				}
				$leftNavbarComponentContents = substr_replace($leftNavbarComponentContents, $doc->saveXML($doc->documentElement, LIBXML_NOEMPTYTAG | LIBXML_NOXMLDECL), $_templateStart, ($_templateEnd-$_templateStart+11));
				file_put_contents($leftNavbarComponentPath, $leftNavbarComponentContents, LOCK_EX);
			}
			libxml_use_internal_errors(false);
		}
		// Add Routes 
		$apiRoutesPath = base_path().DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'api.php';
		$route = <<<ROUTE
// {{objectName}}
Route::post('/{{objectName-lowercase}}/get', [App\Http\Controllers\{{objectName}}Controller::class, 'get'])->name('get-{{objectName-lowercase}}-list');
ROUTE;
		$route = str_replace("{{objectName}}", $jsonObject["object_name"], $route);
		$route = str_replace("{{objectName-lowercase}}", strtolower($jsonObject["object_name"]), $route);
		file_put_contents($apiRoutesPath, "\n".$route, FILE_APPEND);

		$webRoutesPath = base_path().DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php';
		$route = <<<ROUTE
// {{objectName}}
Route::get('/{{objectName-lowercase}}', [App\Http\Controllers\{{objectName}}Controller::class, 'index'])->name('{{objectName-lowercase}}-list');
Route::post('/{{objectName-lowercase}}/save', [App\Http\Controllers\{{objectName}}Controller::class, 'save'])->name('save-{{objectName-lowercase}}');
Route::post('/select{{objectName-lowercase}}', [App\Http\Controllers\{{objectName}}Controller::class, 'loadForSelection'])->name('select{{objectName-lowercase}}');
ROUTE;
		$route = str_replace("{{objectName}}", $jsonObject["object_name"], $route);
		$route = str_replace("{{objectName-lowercase}}", strtolower($jsonObject["object_name"]), $route);
		file_put_contents($webRoutesPath, "\n".$route, FILE_APPEND);

		// ==============
		$componentFilePath = base_path().DIRECTORY_SEPARATOR.
			'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.
			$jsonObject["object_name"].'Component.vue';
		file_put_contents($componentFilePath, $componentContents, LOCK_EX);
		// Lets format the Components file
		/*
		Proper Code however doesnt work somehow
		// $componentContents = file_get_contents($componentFilePath);
		$_templateStart = strpos($componentContents, "<template>");
		$_templateEnd = strrpos($componentContents, "</template>");
		$componentContentsForFormat = substr($componentContents, $_templateStart, ($_templateEnd-$_templateStart+11));
		$componentContentsForFormat = str_replace(":", "attr-subs-colon", $componentContentsForFormat);
		$componentContentsForFormat = str_replace("@", "attr-subs-atrate", $componentContentsForFormat);
		log::info("A".$componentContentsForFormat."~~");
		libxml_clear_errors();
		$doc2 = new \DOMDocument();
		$doc2->strictErrorChecking = false;
		$doc2->preserveWhiteSpace = false;
		$doc2->formatOutput = true;
		// Suppress errors caused by malformed HTML
		libxml_use_internal_errors(true);
		// if (!empty($componentContentsForFormat) && simplexml_load_string(trim($componentContentsForFormat))) {
			if( $doc2->loadXML(trim($componentContentsForFormat)) ){
				$documentContents = $doc2->saveXML($doc2->documentElement, LIBXML_NOXMLDECL);
				$documentContents = str_replace("attr-subs-colon", ':', $componentContentsForFormat);
				$documentContents = str_replace("attr-subs-atrate", '@', $componentContentsForFormat);
				$componentContents = substr_replace($componentContents, $documentContents, $_templateStart, ($_templateEnd-$_templateStart+11));
				file_put_contents($componentFilePath, $componentContents, LOCK_EX);
			}
			else{
				log::info("issue 1 :: ". libxml_get_last_error()->message);
			}
		// }
		// else{
		// 	log::info("issue 2 :: ");
		// 	foreach (libxml_get_errors() as $error) {
		// 		echo "Error: " . $error->message . "<br>";
		// 	}
		// }
		libxml_use_internal_errors(false);
		*/
		// PENDING::Install the component within the app.js
		$appJsPath = base_path().DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'app.js';
		$appJsContents = file_get_contents($appJsPath);
		$componentSearchForImport = $readmeContentsArray["component_searchfor_import"];
		if( !strrpos($appJsContents, $componentSearchForImport) > 0 ){
			// This means that the import is not found
			$pattern = '/^import .*?\n/m';
			// Find all matches
			preg_match_all($pattern, $appJsContents, $matches);

			// If matches are found, get the last match
			if (!empty($matches[0])) {
				$lastStatement = end($matches[0]);
				$nextLinePosition = strpos($appJsContents, $lastStatement) + strlen($lastStatement);
				$appJsContents = substr_replace($appJsContents, "\n// ".$jsonObject["object_label"]."\n".$componentSearchForImport."\n", $nextLinePosition, 0);
			}
		}
		
		$componentSearchForDecl = $readmeContentsArray["component_searchfor_decl"];
		if( !strrpos($appJsContents, $componentSearchForDecl) > 0 ){
			$pattern = '/^app\.component\(.*?\n/m';
			preg_match_all($pattern, $appJsContents, $matches);
			if (!empty($matches[0])) {
				$lastStatement = end($matches[0]);
				$nextLinePosition = strpos($appJsContents, $lastStatement) + strlen($lastStatement);
				$appJsContents = substr_replace($appJsContents, "\n// ".$jsonObject["object_label"]."\n".$componentSearchForDecl."\n", $nextLinePosition, 0);
			}
		}
		file_put_contents($appJsPath, $appJsContents);
		return response()->json(["status" => 1]);
	}

	public function saveConfig(Request $request){
		$input = $request->all();
		// log::info($input);
		// return response()->json(["status" => 1]);
		$response = [];
		if( isset($input["db"]) && isset($input["tbl"]) && isset($input["object"]) && isset($input["object"]["columns"])){
			\BriqueAdminCreator\Models\ObjectConfig::updateOrCreate(
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
			$_objectConfig = \BriqueAdminCreator\Models\ObjectConfig::where('db_name', $input["db"])->where('table_name', $input["tbl"])->first();
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

}
