<?php
namespace BriqueAdminCreator;

use App\Models\PlatformObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File; //custom
use Illuminate\Support\Facades\Storage; //custom

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
			if( !in_array($tableValues[0], ["failed_jobs", "migrations", "password_reset_tokens", "password_resets", "personal_access_tokens", "users", "role", "platform_object", "role_object_mapping"]) )
				array_push($tableNames, $tableValues[0]);
		}

		return view('brique-admin-creator::creatorhome2', compact("tableNames"));
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
		$allColumns = [];
		$sortColumns = [];
		$fileUpload = [];
		// write code to convert string similar to client_name to ClientName
		$objectName = $jsonObject["object_name"];
		$objectName = str_replace(' ', '', ucwords(str_replace('_', ' ', $objectName)));
		$objectLabel = ucwords(str_replace("_", " ", $objectName));
		// $objectName = ucfirst($objectName);
		// $objectLabel = ucfirst($objectLabel);
		$jsonObject["object_name"] = $objectName;
		$jsonObject["object_label"] = $objectLabel;
		$routeObjectName = str_replace(' ', ' ', $jsonObject["object_name"]);		
		log::info($jsonObject);
		foreach($jsonObject['columns'] as $column){
			if($column["type"] != 'divider'){
				array_push($allColumns, $column);
				if($column["form"]["type"]== 'fileupload')
					array_push($fileUpload, $column);
			}
		}
		// MODEL - replace tokens
		$modelContents = ModelService::generateModelContent($objectName, $jsonObject['tbl'], $allColumns);
		// =====================
		if( isset($jsonObject["auth_required"]) && $jsonObject["auth_required"] != ''){
			$authRequired = $jsonObject["auth_required"];
		}
		// =====================
		$modelResourceContent = ModelService::generateModelResourceContent($objectName, $allColumns, $authRequired);
		// log::info($modelResourceContent);
		// =====================
		// Create the action file 
		$actionsServiceContents = file_get_contents(__DIR__.'/storage/app/ActionsService.txt');
		// if (!file_exists(base_path().DIRECTORY_SEPARATOR.
		// 'app'.DIRECTORY_SEPARATOR.'Services'.DIRECTORY_SEPARATOR.'ActionsService.php')) {
		// 	mkdir(base_path().DIRECTORY_SEPARATOR.
		// 	'app'.DIRECTORY_SEPARATOR.'Services'.DIRECTORY_SEPARATOR.'ActionsService.php', 0777, true);
		// }
		// file_put_contents(base_path().DIRECTORY_SEPARATOR.
		// 	'app'.DIRECTORY_SEPARATOR.'Services'.DIRECTORY_SEPARATOR.'ActionsService.php', $actionsServiceContents, LOCK_EX);
		$servicesDir = base_path('app/Services');
		if (!file_exists($servicesDir)) {
			mkdir($servicesDir, 0777, true);
		}
		file_put_contents($servicesDir . DIRECTORY_SEPARATOR . 'ActionsService.php', $actionsServiceContents, LOCK_EX);
		// =====================
		$exportContents = ModelService::generateExportContent($objectName, $allColumns);
		// log::info($exportContents);
		// =====================
		$controllerContents = ControllerService::generateControllerContent($jsonObject);
		// log::info($controllerContents);
		// =====================
		$webRoutesPath = base_path().DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php';
		$route = ModelService::generateWebRouteContent($jsonObject);
		// Check if the route already exists in the web.php file
		$webRoutesContents = file_get_contents($webRoutesPath);
		if (strpos($webRoutesContents, $route) === false) {
			file_put_contents($webRoutesPath, "\n".$route, FILE_APPEND);
		}
		// 
		
		$addRouteForSeparatePage = <<<ROUTE
Route::get('/{{objectName-lowercase}}/add', [App\Http\Controllers\{{objectName}}Controller::class, 'add'])->name('add-{{objectName-lowercase}}-page');
ROUTE;
		if($jsonObject["form_mode"] === 'different_page'){
			$addRouteForSeparatePage = str_replace("{{objectName}}", $routeObjectName, $addRouteForSeparatePage);
			$addRouteForSeparatePage = str_replace("{{objectName-lowercase}}", strtolower($routeObjectName), $addRouteForSeparatePage);
			$route .= "\n".$addRouteForSeparatePage;
		}

		$viewRouteForSeparatePage = <<<ROUTE
Route::get('/{{objectName-lowercase}}/view/{id}', [App\Http\Controllers\{{objectName}}Controller::class, 'view'])->name('view-{{objectName-lowercase}}-page');
ROUTE;
		if($jsonObject["view_mode"] === 'different_page'){
			$viewRouteForSeparatePage = str_replace("{{objectName}}", $routeObjectName, $viewRouteForSeparatePage);
			$viewRouteForSeparatePage = str_replace("{{objectName-lowercase}}", strtolower($routeObjectName), $viewRouteForSeparatePage);
			$route .= "\n".$viewRouteForSeparatePage;
		}
		// log::info($route);
		// Add API Routes 
		$apiRoutesPath = base_path().DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'api.php';
		$apiRoute = <<<ROUTE
// {{objectName}}	
Route::post('/{{objectName-lowercase}}/get', [App\Http\Controllers\{{objectName}}Controller::class, 'get'])->name('get-{{objectName-lowercase}}-list');
Route::post('/{{objectName-lowercase}}/get-record/{id}', [App\Http\Controllers\{{objectName}}Controller::class, 'getRecord'])->name('get-{{objectName-lowercase}}-record');
ROUTE;
		$apiRoute = str_replace("{{objectName}}", $routeObjectName, $apiRoute);
		$apiRoute = str_replace("{{objectName-lowercase}}", strtolower($routeObjectName), $apiRoute);
		$apiRoutesContents = file_get_contents($apiRoutesPath);
		if (strpos($apiRoutesContents, $apiRoute) === false) {
			file_put_contents($apiRoutesPath, "\n".$apiRoute, FILE_APPEND);
		}
		// log::info($apiRoute);
		// =====================
		$componentContents = ComponentService::generateMainComponentContent($jsonObject, $allColumns);
		// log::info($componentContents);

		$componentAddEditFormContents = ComponentService::generateComponentAddEditContent($jsonObject, $allColumns);
		// log::info($componentAddEditFormContents);

		$componentViewContents = ComponentService::generateComponentViewContent($jsonObject, $allColumns);
		// log::info($componentViewContents);

		$monthYearPickerContents = file_get_contents(__DIR__.'/storage/app/MonthYearPicker.txt');
		$directoryPath = base_path().DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'common';
		if (!file_exists($directoryPath)) {
			mkdir($directoryPath, 0777, true);
		}
		$filePath = $directoryPath.DIRECTORY_SEPARATOR.'MonthYearPicker.vue';
		file_put_contents($filePath, $monthYearPickerContents, LOCK_EX);

		//========== Events code =======
		$componentEventsContents = file_get_contents(__DIR__.'/storage/app/Event/Events.txt');
		$componentEventsContents = str_replace("{{objectName}}", $jsonObject["object_name"], $componentEventsContents);
		$componentEventsContents = str_replace("{{objectName-lowercase}}", strtolower($jsonObject["object_name"]), $componentEventsContents);
		// log::info($componentEventsContents);
		if( isset($jsonObject["after_event"]) && $jsonObject["after_event"]){
			// Check if the directory exists, create it if it doesn't
			$directoryPath = base_path('app/Events');
			if (!file_exists($directoryPath)) {
				mkdir($directoryPath, 0777, true);
			}
			// Define the file path
			$filePath = $directoryPath . DIRECTORY_SEPARATOR . $jsonObject["object_name"] . 'Saved.php';
			// Check if the file exists
			if (file_exists($filePath)) {
				// If the file exists, append the new content to the existing file
				file_put_contents($filePath, $componentEventsContents, LOCK_EX);
			} else {
				// If the file does not exist, create a new file with the content
				file_put_contents($filePath, $componentEventsContents, LOCK_EX);
			}
		}
		//========== Events code =======
		//========== Listerners code =======
		$componentListenersContents = file_get_contents(__DIR__.'/storage/app/Event/Listeners.txt');
		$componentListenersContents = str_replace("{{objectName}}", $jsonObject["object_name"], $componentListenersContents);
		$componentListenersContents = str_replace("{{objectName-lowercase}}", strtolower($jsonObject["object_name"]), $componentListenersContents);
		// log::info($componentListenersContents);
		if( isset($jsonObject["after_event"]) && $jsonObject["after_event"]){
			$directoryPath = base_path('app/Listeners');
			if (!file_exists($directoryPath)) {
				mkdir($directoryPath, 0777, true);
			}
			// Define the file path
			$filePath = $directoryPath . DIRECTORY_SEPARATOR .'Handle'.$jsonObject["object_name"] . 'Saved.php';
			// Check if the file exists
			if (file_exists($filePath)) {
				// If the file exists, append the new content to the existing file
				file_put_contents($filePath, $componentListenersContents, LOCK_EX);
			} else {
				// If the file does not exist, create a new file with the content
				file_put_contents($filePath, $componentListenersContents, LOCK_EX);
			}
		}
		//========== Listerners code =======
		// if( $uniqueColumn != null && !empty($uniqueColumn) ){
		// 	$componentContents = str_replace('{{uniqueColumn}}', $uniqueColumn["name"], $componentContents);
		// 	$componentContents = str_replace('{{uniqueColumnLabel}}', $uniqueColumn["frm_label"], $componentContents);
		// }
		// ========== allColumns ===========
		$readmeContentsArray = ModelService::generateReadmeContent($jsonObject, $allColumns);
		$readmeContents = $readmeContentsArray["readme"];
		$componentMixinContents = $readmeContentsArray["mixin"];
		// =====================

		// Install Model and resource in the folder
		// Model
		$modelDir = base_path('app/Models');
		if (!file_exists($modelDir)) {
			mkdir($modelDir, 0777, true);
		}
		if (isset($jsonObject["make_model"]) && $jsonObject["make_model"]) {
			file_put_contents($modelDir . DIRECTORY_SEPARATOR . $jsonObject["object_name"] . '.php', $modelContents, LOCK_EX);
		}

		// Resource
		$resourceDir = base_path('app/Http/Resources');
		if (!file_exists($resourceDir)) {
			mkdir($resourceDir, 0777, true);
		}
		if (isset($jsonObject["make_resource"]) && $jsonObject["make_resource"]) {
			file_put_contents($resourceDir . DIRECTORY_SEPARATOR . $jsonObject["object_name"] . 'Resource.php', $modelResourceContent, LOCK_EX);
		}

		// Controller
		$controllerDir = base_path('app/Http/Controllers');
		if (!file_exists($controllerDir)) {
			mkdir($controllerDir, 0777, true);
		}
		if (isset($jsonObject["make_controller"]) && $jsonObject["make_controller"]) {
			file_put_contents($controllerDir . DIRECTORY_SEPARATOR . $jsonObject["object_name"] . 'Controller.php', $controllerContents, LOCK_EX);
		}
		// Export
		$exportsDir = base_path('app/Exports');
		if (!file_exists($exportsDir)) {
			mkdir($exportsDir, 0777, true);
		}
		$exportFilePath = $exportsDir . DIRECTORY_SEPARATOR . $jsonObject["object_name"] . 'Export.php';
		file_put_contents($exportFilePath, $exportContents, LOCK_EX);

		// ==============
		$componentDirectoryPath = base_path('resources/js/components/' . strtolower($jsonObject["object_name"]));
		log::info($componentDirectoryPath);
		// Check if the directory exists, create it if it doesn't
		if (!file_exists($componentDirectoryPath)) {
			mkdir($componentDirectoryPath, 0777, true);
		}
		// Define the file paths
		$mainComponentFilePath = $componentDirectoryPath . DIRECTORY_SEPARATOR . $jsonObject["object_name"] . 'Component.vue';
		$addEditComponentFilePath = $componentDirectoryPath . DIRECTORY_SEPARATOR . 'AddEdit' . $jsonObject["object_name"] . 'Component.vue';
		$viewComponentFilePath = $componentDirectoryPath . DIRECTORY_SEPARATOR . 'View' . $jsonObject["object_name"] . 'Component.vue';

		// Save the files
		if(isset($jsonObject["make_component"]) && $jsonObject["make_component"]){
			file_put_contents($mainComponentFilePath, $componentContents, LOCK_EX);
			file_put_contents($addEditComponentFilePath, $componentAddEditFormContents, LOCK_EX);
			file_put_contents($viewComponentFilePath, $componentViewContents, LOCK_EX);
		}
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
		// Define the import statements
		// $importStatements = [
		// 	"\n//{$jsonObject["object_name"]}\nimport {$jsonObject["object_name"]}Component from './components/{$jsonObject["object_name"]}/{$jsonObject["object_name"]}Component.vue';",
		// 	"import AddEdit{$jsonObject["object_name"]}Component from './components/{$jsonObject["object_name"]}/AddEdit{$jsonObject["object_name"]}Component.vue';",
		// 	"import View{$jsonObject["object_name"]}Component from './components/{$jsonObject["object_name"]}/View{$jsonObject["object_name"]}Component.vue';\n"
		// ];
		$importStatements = [
			"\n//{$jsonObject["object_name"]}\nimport {$jsonObject["object_name"]}Component from './components/".strtolower($jsonObject["object_name"])."/{$jsonObject["object_name"]}Component.vue';",
			"import AddEdit{$jsonObject["object_name"]}Component from './components/".strtolower($jsonObject["object_name"])."/AddEdit{$jsonObject["object_name"]}Component.vue';",
			"import View{$jsonObject["object_name"]}Component from './components/".strtolower($jsonObject["object_name"])."/View{$jsonObject["object_name"]}Component.vue';\n"
		];
		// Add import statements if they don't already exist
		foreach ($importStatements as $importStatement) {
			if (strpos($appJsContents, $importStatement) === false) {
				// This means that the import is not found
				$pattern = '/^import .*?\n/m';
				// Find all matches
				preg_match_all($pattern, $appJsContents, $matches);
				// If matches are found, get the last match
				if (!empty($matches[0])) {
					$lastStatement = end($matches[0]);
					$nextLinePosition = strpos($appJsContents, $lastStatement) + strlen($lastStatement);
					$appJsContents = substr_replace($appJsContents, $importStatement, $nextLinePosition, 0);
				}else{
					// If no matches are found, add the import statement at the top of the file
					$appJsContents = $importStatement . "\n" . $appJsContents;
				}
			}
		}
		// Define the component registration statements
		$registrationStatements = [
			"\n//{$jsonObject["object_name"]}\napp.component('".strtolower($jsonObject["object_name"])."-component', ".$jsonObject["object_name"]."Component);",
			"app.component('addedit-".strtolower($jsonObject["object_name"])."-component', AddEdit".$jsonObject["object_name"]."Component);",
			"app.component('view-".strtolower($jsonObject["object_name"])."-component', View".$jsonObject["object_name"]."Component);\n"
		];
		// Add import statements if they don't already exist
		foreach ($registrationStatements as $registrationsStatement) {
			if (strpos($appJsContents, $registrationsStatement) === false) {
				$pattern = '/^app\.component\(.*?\n/m';
				preg_match_all($pattern, $appJsContents, $matches);
				if (!empty($matches[0])) {
					$lastStatement = end($matches[0]);
					$nextLinePosition = strpos($appJsContents, $lastStatement) + strlen($lastStatement);
					$appJsContents = substr_replace($appJsContents, $registrationsStatement, $nextLinePosition, 0);
				}else{
					// If no matches are found, add the import statement at the top of the file
					$appJsContents = $registrationsStatement . "\n" . $appJsContents;
				}
			}
		}
		// log::info($appJsContents);
		file_put_contents($appJsPath, $appJsContents);
			// try {
			// 	PlatformObject::create([
			// 		"name" => $jsonObject["object_label"],
			// 		"role_id" => '',
			// 		"can_export" => $jsonObject["can_export"],
			// 		"can_add_edit_duplicate" => $jsonObject["can_add_edit_duplicate"],
			// 		"can_delete" => $jsonObject["can_delete"],
			// 		"created_by" => 1,
			// 	]);
			// } catch (\Throwable $th) {
			// 	throw $th;
			// }
		return response()->json(["status" => 1]);
	}

	public function saveConfig(Request $request){
		$input = $request->all();
		// return response()->json(["status" => 1]);
		$response = [];
		if( isset($input["db"]) && isset($input["tbl"]) && isset($input["object"]) && isset($input["object"]["columns"])){
			\BriqueAdminCreator\Models\ObjectConfig::updateOrCreate(
				[ 'object_settings' => json_encode($input["object"]) ],
				[ 'db_name' => $input["db"], 'table_name' => $input["tbl"] ]
			);
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

	//Custom Function for directory check
	public function checkDirectory(Request $request){
        try {
			$directory = base_path($request->input('directory'));
			$baseFileName = $request->input('baseFileName');
			$latestVersion = 0;
			$latestFileName = '';

			if (File::exists($directory)) {
				$files = File::files($directory);
				foreach ($files as $file) {
					if (strpos($file->getFilename(), $baseFileName) === 0) {
						preg_match('/_v(\d+)_/', $file->getFilename(), $matches);
						if (isset($matches[1]) && $matches[1] > $latestVersion) {
							$latestVersion = (int)$matches[1];
                            $latestFileName = $file->getFilename();
						}
					}
				}
			}
	
			return response()->json(['latestVersion' => $latestVersion,'latestFileName' => $latestFileName]);

		} catch (\Exception $e) {
			Log::error('Error in checkDirectory: ' . $e->getMessage());
			return response()->json(['error' => 'Internal Server Error'], 500);
		}
    }

	//Custom Function for saving file
    public function saveFile(Request $request){
        try {
			$currentJSON = [];
            $directory = base_path($request->input('directory')); // Use base_path to get the root directory
            $baseFileName = $request->input('baseFileName');
            $fileContent = $currentJSON = $request->input('fileContent');
            $versionName = $request->input('versionName');
            $fileName = "{$baseFileName}.json";
            $filePath = "{$directory}/{$fileName}";
			$latestVersion = 0;

            // Check if the directory exists and get the latest version number
            if (File::exists($directory)) {
                $files = File::files($directory);
                foreach ($files as $file) {
                    if ($file->getFilename() === "{$baseFileName}.json") {
                        $existingContent = json_decode(File::get($file->getPathname()), true);
                        $existingKeys = array_keys($existingContent);
						$latestVersion = count($existingKeys);
                        foreach ($existingKeys as $key) {
                            preg_match('/^v(\d+)_/', $key, $matches);
                            if (isset($matches[1]) && $matches[1] >= $latestVersion) 
                                $latestVersion = (int)$matches[1];
                        }
                    }
                }
            }

			// If versionName is provided, use it; otherwise, use the calculated latestVersion
			$newKey = $versionName ? $versionName : "v" . ($latestVersion + 1);
            if (!File::exists($directory))
				File::makeDirectory($directory, 0755, true);

            if (File::exists($filePath)) {
                $existingContent = json_decode(File::get($filePath), true);
                $fileContent = array_merge($existingContent, [$newKey => $fileContent[array_key_first($fileContent)]]);
            } else
                $fileContent = [$newKey => $fileContent[array_key_first($fileContent)]];

            File::put($filePath, json_encode($fileContent, JSON_PRETTY_PRINT));

			return response()->json(['status' => 'success', 'msg' => 'File has been saved successfully.', 'filePath' => $filePath, 'currentObject'=> $currentJSON, 'version' => $latestVersion]);

        } catch (\Exception $e) {
            Log::error('Error in saveFile: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

	//custom function for getting table json
	public function getTableJSON(Request $request) {
		$tableName = $request->input('tableName');
		$directory = base_path('creator');
		$filePath = "{$directory}/{$tableName}.json";
		$content = [];

		if (File::exists($filePath)) {
			$content = json_decode(File::get($filePath), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error('Error parsing JSON content: ' . json_last_error_msg());
				$content = [];
			} else {
				$content = array_map(function($key, $value) {
					return ['key' => $key, 'value' => $value];
				}, array_keys($content), $content);
			}
		}
	
		return response()->json($content);
	}

	//custom function for downloading file
	public function downloadFile($fileName, $versionName) {
		$filePath = base_path("creator/$fileName");
		if (!file_exists($filePath))
            return response()->json(['error' => 'File not found.'], 404);

		$fileContent = json_decode(file_get_contents($filePath), true);

		if (!isset($fileContent[$versionName])) {
			return response()->json(['error' => 'Version not found.'], 404);
		}
		$versionContent = json_encode($fileContent[$versionName], JSON_PRETTY_PRINT);

		// Define the file path in a writable directory
		$storagePath = storage_path('app/public/currentObject.json');
		// Write the JSON content to the file
		file_put_contents($storagePath, $versionContent);
		// Return the file as a response for download
		return response()->download($storagePath)->deleteFileAfterSend(true);
    }
}
