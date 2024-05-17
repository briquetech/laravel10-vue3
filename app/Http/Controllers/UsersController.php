<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller{

	//
	public function index(){
		$component = 'users-component';
		return view('common.index', compact('component'));
	}

	public function get(Request $request){
		$input = $request->all();
		$usersList = \App\Models\Users::select("*");
		// SIMPLE & ADVANCED SEARCH ClAUSE
		$searchType = "simple";
		if( isset($input["search"]) )
			$searchType = $input["search"];
		if( $searchType == "simple" ){
			if( isset($input["q"]) && strlen(trim($input["q"])) > 0 ){
				$usersList = $usersList->where(function($query) use ($input) {
					$query = $query->where('name', 'like', '%'.trim($input['q']).'%')->orWhere('email', 'like', '%'.trim($input['q']).'%');
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
								$usersList = $usersList->whereNot(function($query) use ($filter) {
									$query = $query->where('name', trim($filter['search_for_value']))->orWhere('email', trim($filter['search_for_value']));
								});
								break;
							case 1: // EQUALS
								$usersList = $usersList->where(function($query) use ($filter) {
									$query = $query->where('name', trim($filter['search_for_value']))->orWhere('email', trim($filter['search_for_value']));
								});
								break;
							case 22: // CONTAINS
								$usersList = $usersList->where(function($query) use ($filter) {
									$query = $query->where('name', 'like', '%'.trim($filter['search_for_value']).'%')->orWhere('email', 'like', '%'.trim($filter['search_for_value']).'%');
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
							$usersList = $usersList->where($filter['property'], $condition, (($filter['type'] == 'text' || $filter['type'] == 'dropdown') ? $filter['search_for_value'] : $filter['from_value']));
						}
						else if( $searchType == 'between' ){
							$usersList = $usersList->where($filter['property'], '>=', $filter['from_value'])->where($filter['property'], '<=', $filter['to_value']);
						}
					}
				}
			}
		}
		// SIMPLE & ADVANCED SEARCH ClAUSE
		if( isset($input["active"]) && is_numeric($input["active"]) && $input["active"] == 1 ){
			$usersList = $usersList->where('status', 1);
		}
		if( isset($input["sortBy"]) && strlen(trim($input["sortBy"])) > 0 ){
			if( trim($input["sortOrder"]) == "desc" )
				$usersList = $usersList->orderByDesc(trim($input["sortBy"]));
			else
				$usersList = $usersList->orderBy(trim($input["sortBy"]));
		}
		if( isset($input["page"]) )
			$usersList = $usersList->paginate(10);
		else{
			if( isset($input["download"]) ){
				$export = new \App\Exports\UsersExport($usersList);
				switch( $input["download"] ){
					case 'XLSX':
						return Excel::download($export, 'UsersList.xlsx', \Maatwebsite\Excel\Excel::XLSX);
						break;
					case 'XLS':
						return Excel::download($export, 'UsersList.xlsx', \Maatwebsite\Excel\Excel::XLS);
						break;
					case 'CSV':
						return Excel::download($export, 'UsersList.csv', \Maatwebsite\Excel\Excel::CSV, [
							'Content-Type' => 'text/csv',
						]);
						break;
					case 'PDF':
						return Excel::download($export, 'UsersList.pdf', \Maatwebsite\Excel\Excel::TCPDF);
						break;
					case 'ODS':
						return Excel::download($export, 'UsersList.ods', \Maatwebsite\Excel\Excel::ODS);
						break;
				}
			}
			else
				$usersList = $usersList->get();
		}
		$usersList->append(['actions']);
		return $usersList->toJson();
	}

	// Save users
	public function save(Request $request){
		$input = $request->all();
		if( isset($input["users"]) ){
			$users = $input["users"];
			$objectToSave = [];
			$checkTitle = true;
			if( $users["action"] == "status" ){
				$objectToSave["status"] = $users["status"];
				if( $users["status"] <= 0 )
					$checkTitle = false;
			}
			if( $users["action"] == "details" ){
				$rules = [
					'name' => 'required|string',
					'email' => 'required|email:filter',
					'password' => 'required|string',
					'employee_code' => 'required|string',
				];
				$validator = Validator::make($users, $rules);
				if ($validator->fails()) {
					return response()->json(["status" => -1, "messages" => array_merge(...array_values($validator->errors()->toArray())) ]);
				}
				$objectToSave = $users;
				if(!isset($objectToSave["created_by"]))
					$objectToSave["created_by"] = Auth::id();
				if( $checkTitle && \App\Models\Users::where('email', $users["email"])->where( "id", "!=", $users["id"] )->where('status', 1)->count() > 0 )
					return response()->json(["status" => -1, "messages" => ["Email has to be unique."]]);
				$objectToSave['password'] = Hash::make($objectToSave['password']);
			}
			\App\Models\Users::updateOrCreate( [ "id" => $users["id"] ], $objectToSave );
			return response()->json(["status" => 1]);
		}
		else{
			return response()->json(["status" => -100, "messages" => ["Data for Users is missing."]]);
		}
	}
}
