<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
		return view('common.index', compact('component', 'current_user_id'));
	}

	public function get(Request $request)
	{
		$input = $request->all();
		$userList = \App\Models\User::with('reporting_to_user', 'role')->select("*");
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
			if ($user["action"] == "details") {
				$rules = [
					'name' => 'required|string',
					'email' => 'required|email:filter',
					'password' => 'required|string',
					'department' => 'required|string',
					'employee_code' => 'required|string',
					'reporting_to' => 'required',
					'role_id' => 'required',

				];
				$validator = Validator::make($user, $rules);
				if ($validator->fails()) {
					return response()->json(["status" => -1, "messages" => array_merge(...array_values($validator->errors()->toArray()))]);
				}
				$objectToSave = $user;
				if (!isset($objectToSave["created_by"]))
					$objectToSave["created_by"] = Auth::id();
				if ($checkTitle && \App\Models\User::where('email', $user["email"])->where("id", "!=", $user["id"])->where('status', 1)->count() > 0)
					return response()->json(["status" => -1, "messages" => ["Email has to be unique."]]);
				// Reset password if required and present 
				if( isset($objectToSave['password']) )
					$objectToSave['password'] = Hash::make($objectToSave['password']);
			}
			\App\Models\User::updateOrCreate(["id" => $user["id"]], $objectToSave);
			return response()->json(["status" => 1]);
		} else {
			return response()->json(["status" => -100, "messages" => ["Data for User is missing."]]);
		}
	}
}
