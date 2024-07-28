<?php

namespace App\Http\Controllers;

use App\Models\PlatformObject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{

	//
	public function index()
	{
		$component = 'user-component';
		$current_user_id = auth()->id();
		$maxNoOfUsers = SettingsService::getSettingValue("license_users");
		if( User::count() >= $maxNoOfUsers+1 ){
			if( auth()->user()->role_id == 1 ){
				$all_permissions = "111";
			}
			else
				$all_permissions = "100";
		}
		else{
			if( auth()->user()->role_id == 1 ){
				$all_permissions = "111";
			}
			else{
				// Common code for authorization
				$platformObject = PlatformObject::where('name', 'User')->first();
				$permissions = DB::table('role_object_mapping')
					->where('role_id', auth()->user()->role_id)
					->where('platform_object_id', $platformObject->id)
					->first();
				$all_permissions = "1";
				if( $permissions !== null ){
					$all_permissions .= ($permissions->can_add_edit ? "1": "0");
					$all_permissions .= ($permissions->can_delete ? "1": "0");
				}
				else
					abort(403);
			}
		}
		$param1 = $maxNoOfUsers;
		return view('common.index', compact('component', 'current_user_id', 'all_permissions', 'param1'));
	}

	public function get(Request $request){
		$input = $request->all();
		$userList = \App\Models\User::with('reporting_to_user', 'role', 'department', 'designation')->select("*");
		// SIMPLE & ADVANCED SEARCH ClAUSE
		$searchType = "simple";
		if (isset($input["search"]))
			$searchType = $input["search"];
		if ($searchType == "simple") {
			if (isset($input["q"]) && strlen(trim($input["q"])) > 0) {
				$userList = $userList->where(function ($query) use ($input) {
					$query = $query->where('name', 'like', '%' . trim($input['q']) . '%')
						->orWhere('department', 'like', '%' . trim($input['q']) . '%')
						->orWhere('employee_code', 'like', '%' . trim($input['q']) . '%')
						->orWhere('reporting_to', 'like', '%' . trim($input['q']) . '%')->orWhere('role_id', 'like', '%' . trim($input['q']) . '%');
				});
			}
		} else {
			// Advanced search
			if (isset($input['advfilters']) && is_array($input['advfilters']) && count($input['advfilters']) > 0) {
				foreach ($input['advfilters'] as $filter) {
					if ($filter['property'] == "__q") {
						switch ($filter['condition']) {
							case 0: // NOT EQUALS
								$userList = $userList->whereNot(function ($query) use ($filter) {
									$query = $query->where('name', trim($filter['search_for_value']))
										->orWhere('department', trim($filter['search_for_value']))
										->orWhere('employee_code', trim($filter['search_for_value']))
										->orWhere('reporting_to', trim($filter['search_for_value']))->orWhere('role_id', trim($filter['search_for_value']));
								});
								break;
							case 1: // EQUALS
								$userList = $userList->where(function ($query) use ($filter) {
									$query = $query->where('name', trim($filter['search_for_value']))
										->orWhere('department', trim($filter['search_for_value']))
										->orWhere('employee_code', trim($filter['search_for_value']))
										->orWhere('reporting_to', trim($filter['search_for_value']))->orWhere('role_id', trim($filter['search_for_value']));
								});
								break;
							case 22: // CONTAINS
								$userList = $userList->where(function ($query) use ($filter) {
									$query = $query->where('name', 'like', '%' . trim($filter['search_for_value']) . '%')
										->orWhere('department', 'like', '%' . trim($filter['search_for_value']) . '%')
										->orWhere('employee_code', 'like', '%' . trim($filter['search_for_value']) . '%')
										->orWhere('reporting_to', 'like', '%' . trim($filter['search_for_value']) . '%')->orWhere('role_id', 'like', '%' . trim($filter['search_for_value']) . '%');
								});
								break;
						}
					} else {
						$condition = '=';
						$searchType = 'direct';
						switch ($filter['condition']) {
							case 0:
								$condition = "!=";
								break;
							case 2:
							case 12:
								$condition = "<=";
								break;
							case 3:
							case 13:
								$condition = ">=";
								break;
							case 5:
							case 15:
								$searchType = 'between';
								break;
						}
						if ($searchType == 'direct') {
							$userList = $userList->where($filter['property'], $condition, (($filter['type'] == 'text' || $filter['type'] == 'dropdown') ? $filter['search_for_value'] : $filter['from_value']));
						} else if ($searchType == 'between') {
							$userList = $userList->where($filter['property'], '>=', $filter['from_value'])->where($filter['property'], '<=', $filter['to_value']);
						}
					}
				}
			}
		}

		// Access control
		if( isset($input['current_user_id']) && $input['current_user_id'] > 0 ){
			$currentUser = User::find($input['current_user_id']);
			if( $currentUser->role_id > 1 ){
				// Apply the permissions only if the user is not an administrator
				$permissions = DB::table("role_object_mapping")
					->join('platform_object', 'role_object_mapping.platform_object_id', '=', 'platform_object.id')
					->select('role_object_mapping.view_records', 'role_object_mapping.can_add_edit', 'role_object_mapping.can_delete')
					->where("role_object_mapping.role_id", $currentUser->role_id)
					->where("platform_object.name", "User")
					->first();
				if( $permissions != null && $permissions->view_records == 2 ){
					// Only view people in your own hierarchy
					$allReportees = [ $input['current_user_id'] ];
					foreach (UserService::getNestedReportees($currentUser->id) as $reportee) {
						$allReportees[] = $reportee->id;
					}
					$userList = $userList->whereIn("id", $allReportees);
				}
			}
		}

		// SIMPLE & ADVANCED SEARCH ClAUSE
		if (isset($input["active"]) && is_numeric($input["active"]) && $input["active"] == 1) {
			$userList = $userList->where('status', 1);
		}
		if (isset($input["sortBy"]) && strlen(trim($input["sortBy"])) > 0) {
			if (trim($input["sortOrder"]) == "desc")
				$userList = $userList->orderByDesc(trim($input["sortBy"]));
			else
				$userList = $userList->orderBy(trim($input["sortBy"]));
		}

		if (isset($input["page"]))
			$userList = $userList->paginate(10);
		else {
			if (isset($input["download"])) {
				$export = new \App\Exports\UserExport($userList);
				switch ($input["download"]) {
					case 'XLSX':
						return Excel::download($export, 'UserList.xlsx', \Maatwebsite\Excel\Excel::XLSX);
						break;
					case 'XLS':
						return Excel::download($export, 'UserList.xlsx', \Maatwebsite\Excel\Excel::XLS);
						break;
					case 'CSV':
						return Excel::download($export, 'UserList.csv', \Maatwebsite\Excel\Excel::CSV, [
							'Content-Type' => 'text/csv',
						]);
						break;
					case 'PDF':
						return Excel::download($export, 'UserList.pdf', \Maatwebsite\Excel\Excel::TCPDF);
						break;
					case 'ODS':
						return Excel::download($export, 'UserList.ods', \Maatwebsite\Excel\Excel::ODS);
						break;
				}
			} else
				$userList = $userList->get();
		}
		$userList->append(['actions']);
		return \App\Http\Resources\UserResource::collection($userList);
		return $userList->toJson();
	}

	public function getUserReportees(Request $request){
		$input = $request->all();
		if (isset($input["user_id"]) ){
			$allReportees = UserService::getNestedReportees($input["user_id"]);
			return response()->json([ "status" => 1, "reportees" => $allReportees ]);
		}
		else
			return response()->json(["status" => -1 ]);
	}

	// Save user
	public function save(Request $request)
	{
		$input = $request->all();
		if (isset($input["user"])) {
			$user = $input["user"];
			$objectToSave = [];
			$checkTitle = true;
			if ($user["action"] == "status") {
				$objectToSave["status"] = $user["status"];
				if ($user["status"] <= 0)
					$checkTitle = false;
			}
			else if ($user["action"] == "details") {
				$rules = [
					'name' => 'required|string',
					'email' => 'required|email:filter',
					'department_id' => 'required',
					'designation_id' => 'required',
					'employee_code' => 'required|string',
					'reporting_to' => 'required',
					'role_id' => 'required',
				];
				$validator = Validator::make($user, $rules);
				if ($validator->fails()) {
					return response()->json(["status" => -1, "messages" => array_merge(...array_values($validator->errors()->toArray()))]);
				}
				if( $user['id'] == 0 ){
					$maxNoOfUsers = SettingsService::getSettingValue("license_users");
					if( User::count() >= $maxNoOfUsers+1 ){
						return response()->json(["status" => -1, "messages" => ["Your license supports adding ".$maxNoOfUsers." only. Please connect with brique.solutions@gmail.com."]]);
					}
				}
				$objectToSave = $user;
				if (!isset($objectToSave["created_by"]))
					$objectToSave["created_by"] = Auth::id();
				// where('email', $user["email"])->
				$count = \App\Models\User::
							where("id", "!=", $user["id"])->where('status', 1)
							->where(function ($query) use ($user) {
								$query = $query->where('email', $user['email'])
									->orWhere('employee_code', $user['employee_code']);
							})->count();
				if ($checkTitle && $count > 0)
					return response()->json(["status" => -1, "messages" => ["Email and Employee Code have to be unique."]]);
				// Reset password if required and present 
				if( ( $objectToSave["id"] == 0 || 
					( $objectToSave["id"] > 0 && isset($objectToSave['change_password']) && $objectToSave['change_password'] == 1 ) ) && 
					isset($objectToSave['password']) && strlen($objectToSave['password']) > 0 ){
					$objectToSave['password'] = Hash::make($objectToSave['password']);
				}
				else{
					unset($objectToSave['password']);
				}
				$newUser = \App\Models\User::updateOrCreate(["id" => $user["id"]], $objectToSave);
				// This is a complex logic.
				$oldHierarchyNodeId = $newUser->hierarchy_node_id;
				// Update the user's hierarchy node id
				$hierarchyNodeId = "";
				if( $newUser->reporting_to > 0 ){
					$hierarchyNodeId = User::find($newUser->reporting_to)->hierarchy_node_id;
				}
				$newUser->hierarchy_node_id = $hierarchyNodeId.$newUser->id.".";
				$newUser->save();
				if( $user["id"] == 0 ){
					// Send
					MailService::notifyNewUserRegistration($newUser->email, $newUser->name, $newUser->email, $user['password']);
				}
				else{
					// check all the reportees of this user and update their hierarchy node ids
					$reportees = User::where('hierarchy_node_id', 'LIKE', $oldHierarchyNodeId.'%')->get();
					foreach ($reportees as $reportee) {
						// iterate over reportees and update each reportee's hierarchy_node_id with the newUser's hierarchy_node_id
						$reportee->hierarchy_node_id = $newUser->hierarchy_node_id.$reportee->id.".";
						$reportee->save();
					}
				}
			}
			return response()->json(["status" => 1]);
		} else {
			return response()->json(["status" => -100, "messages" => ["Data for User is missing."]]);
		}
	}
}
