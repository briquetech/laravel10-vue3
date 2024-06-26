<?php
namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class RoleController extends Controller{

	//
	public function index(){
		$component = 'role-component';
		$current_user_id = auth()->id();
		return view('common.index', compact('component', 'current_user_id'));
	}

	public function get(Request $request){
		$input = $request->all();
		$roleList = \App\Models\Role::select("*");
		// SIMPLE & ADVANCED SEARCH ClAUSE
		$searchType = "simple";
		if( isset($input["search"]) )
			$searchType = $input["search"];
		if( $searchType == "simple" ){
			if( isset($input["q"]) && strlen(trim($input["q"])) > 0 ){
				$roleList = $roleList->where(function($query) use ($input) {
					$query = $query->where('title', 'like', '%'.trim($input['q']).'%');
				});
			}
		}
		else{
			// Advanced search
			if( isset($input['advfilters']) && is_array($input['advfilters']) && count($input['advfilters']) > 0 ){
				foreach($input['advfilters'] as $filter){
					if( $filter['property'] == "__q" ){
						switch($filter['condition']){
							case 0: // NOT EQUALS
								$roleList = $roleList->whereNot(function($query) use ($filter) {
									$query = $query->where('title', trim($filter['search_for_value']));
								});
								break;
							case 1: // EQUALS
								$roleList = $roleList->where(function($query) use ($filter) {
									$query = $query->where('title', trim($filter['search_for_value']));
								});
								break;
							case 22: // CONTAINS
								$roleList = $roleList->where(function($query) use ($filter) {
									$query = $query->where('title', 'like', '%'.trim($filter['search_for_value']).'%');
								});
								break;
						}
					} 
					else{
						$condition = '=';
						$searchType = 'direct';
						switch($filter['condition']){
							case 0: $condition = "!="; break;
							case 2: case 12: $condition = "<="; break;
							case 3: case 13: $condition = ">="; break;
							case 5: case 15: $searchType = 'between'; break;
						}
						if( $searchType == 'direct' ){
							$roleList = $roleList->where($filter['property'], $condition, (($filter['type'] == 'text' || $filter['type'] == 'dropdown') ? $filter['search_for_value'] : $filter['from_value']));
						}
						else if( $searchType == 'between' ){
							$roleList = $roleList->where($filter['property'], '>=', $filter['from_value'])->where($filter['property'], '<=', $filter['to_value']);
						}
					}
				}
			}
		}
		// SIMPLE & ADVANCED SEARCH ClAUSE
		if( isset($input["active"]) && is_numeric($input["active"]) && $input["active"] == 1 ){
			$roleList = $roleList->where('status', 1);
		}
		if( isset($input["sortBy"]) && strlen(trim($input["sortBy"])) > 0 ){
			if( trim($input["sortOrder"]) == "desc" )
				$roleList = $roleList->orderByDesc(trim($input["sortBy"]));
			else
				$roleList = $roleList->orderBy(trim($input["sortBy"]));
		}
		if( isset($input["page"]) )
			$roleList = $roleList->paginate(10);
		else{
			if( isset($input["download"]) ){
				$export = new \App\Exports\RoleExport($roleList);
				switch( $input["download"] ){
					case 'XLSX':
						return Excel::download($export, 'RoleList.xlsx', \Maatwebsite\Excel\Excel::XLSX);
						break;
					case 'XLS':
						return Excel::download($export, 'RoleList.xlsx', \Maatwebsite\Excel\Excel::XLS);
						break;
					case 'CSV':
						return Excel::download($export, 'RoleList.csv', \Maatwebsite\Excel\Excel::CSV, [
							'Content-Type' => 'text/csv',
						]);
						break;
					case 'PDF':
						return Excel::download($export, 'RoleList.pdf', \Maatwebsite\Excel\Excel::TCPDF);
						break;
					case 'ODS':
						return Excel::download($export, 'RoleList.ods', \Maatwebsite\Excel\Excel::ODS);
						break;
				}
			}
			else
				$roleList = $roleList->get();
		}
		$roleList->append(['actions']);
		return \App\Http\Resources\RoleResource::collection($roleList);
		return $roleList->toJson();
	}

	public function getPermittedObjects(Request $request){
		$input = $request->all();
		if( isset($input["role_id"]) ){
			$permittedObjects = Role::find($input["role_id"])->objects()->get()->pluck('title');
			return response()->json(["status" => 1, "permitted_objects" => $permittedObjects]);
		}
		else
			return response()->json(["status" => -1]);
	}

	// Save role
	public function save(Request $request){
		$input = $request->all();
		if( isset($input["role"]) ){
			$role = $input["role"];
			$objectToSave = [];
			$checkTitle = true;
			if( $role["action"] == "status" ){
				$objectToSave["status"] = $role["status"];
				if( $role["status"] <= 0 )
					$checkTitle = false;
			}
			if( $role["action"] == "details" ){
				$rules = [
					'title' => 'required|string',

				];
				$validator = Validator::make($role, $rules);
				if ($validator->fails()) {
					return response()->json(["status" => -1, "messages" => array_merge(...array_values($validator->errors()->toArray())) ]);
				}
				$objectToSave = $role;
				if(!isset($objectToSave["created_by"]))
					$objectToSave["created_by"] = Auth::id();
								if( $checkTitle && \App\Models\Role::where('title', $role["title"])->where( "id", "!=", $role["id"] )->where('status', 1)->count() > 0 )
					return response()->json(["status" => -1, "messages" => ["Title has to be unique."]]);
			}
			\App\Models\Role::updateOrCreate( [ "id" => $role["id"] ], $objectToSave );
			return response()->json(["status" => 1]);
		}
		else{
			return response()->json(["status" => -100, "messages" => ["Data for Role is missing."]]);
		}
	}
}
