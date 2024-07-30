<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Models\PlatformObject;

class DepartmentController extends Controller
{

	//
	public function index()
	{
		$component = 'department-component';
		$current_user_id = auth()->id();
		if (auth()->user()->role_id == 1) {
			$all_permissions = "111";
		} else {
			// Common code for authorization
			$platformObject = PlatformObject::where('name', 'Department')->first();
			$permissions = DB::table('role_object_mapping')
				->where('role_id', auth()->user()->role_id)
				->where('platform_object_id', $platformObject->id)
				->first();
			$all_permissions = "1";
			if ($permissions !== null) {
				$all_permissions .= ($permissions->can_add_edit ? "1" : "0");
				$all_permissions .= ($permissions->can_delete ? "1" : "0");
			} else
				abort(403);
		}
		return view('common.index', compact('component', 'current_user_id', 'all_permissions'));
	}

	public function get(Request $request)
	{
		$input = $request->all();
		$departmentList = \App\Models\Department::with('creator')->select("*");
		// SIMPLE & ADVANCED SEARCH ClAUSE
		$searchType = "simple";
		if (isset($input["search"]))
			$searchType = $input["search"];
		if ($searchType == "simple") {
			if (isset($input["q"]) && strlen(trim($input["q"])) > 0) {
				$departmentList = $departmentList->where(function ($query) use ($input) {
					$query = $query->where('title', 'like', '%' . trim($input['q']) . '%');
				});
				$departmentList = $departmentList->orWhereHas('creator', function ($query) use ($input) {
					$query = $query->where('name', 'like', '%' . trim($input['q']) . '%');
				});
			}
		} else {
			// Advanced search
			if (isset($input['advfilters']) && is_array($input['advfilters']) && count($input['advfilters']) > 0) {
				foreach ($input['advfilters'] as $filter) {
					if ($filter['property'] == "__q") {
						switch ($filter['condition']) {
							case 0: // NOT EQUALS
								$departmentList = $departmentList->whereNot(function ($query) use ($filter) {
									$query = $query->where('title', trim($filter['search_for_value']));
								});
								break;
							case 1: // EQUALS
								$departmentList = $departmentList->where(function ($query) use ($filter) {
									$query = $query->where('title', trim($filter['search_for_value']));
								});
								break;
							case 22: // CONTAINS
								$departmentList = $departmentList->where(function ($query) use ($filter) {
									$query = $query->where('title', 'like', '%' . trim($filter['search_for_value']) . '%');
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
							$departmentList = $departmentList->where($filter['property'], $condition, (($filter['type'] == 'text' || $filter['type'] == 'dropdown') ? $filter['search_for_value'] : $filter['from_value']));
						} else if ($searchType == 'between') {
							$departmentList = $departmentList->where($filter['property'], '>=', $filter['from_value'])->where($filter['property'], '<=', $filter['to_value']);
						}
					}
				}
			}
		}
		// SIMPLE & ADVANCED SEARCH ClAUSE
		if (isset($input["active"]) && is_numeric($input["active"]) && $input["active"] == 1) {
			$departmentList = $departmentList->where('status', 1);
		}
		if (isset($input["sortBy"]) && strlen(trim($input["sortBy"])) > 0) {
			if (trim($input["sortOrder"]) == "desc")
				$departmentList = $departmentList->orderByDesc(trim($input["sortBy"]));
			else
				$departmentList = $departmentList->orderBy(trim($input["sortBy"]));
		}
		if (isset($input["page"]))
			$departmentList = $departmentList->paginate(10);
		else {
			if (isset($input["download"])) {
				$export = new \App\Exports\DepartmentExport($departmentList);
				switch ($input["download"]) {
					case 'XLSX':
						return Excel::download($export, 'DepartmentList.xlsx', \Maatwebsite\Excel\Excel::XLSX);
						break;
					case 'XLS':
						return Excel::download($export, 'DepartmentList.xlsx', \Maatwebsite\Excel\Excel::XLS);
						break;
					case 'CSV':
						return Excel::download($export, 'DepartmentList.csv', \Maatwebsite\Excel\Excel::CSV, [
							'Content-Type' => 'text/csv',
						]);
						break;
					case 'PDF':
						return Excel::download($export, 'DepartmentList.pdf', \Maatwebsite\Excel\Excel::TCPDF);
						break;
					case 'ODS':
						return Excel::download($export, 'DepartmentList.ods', \Maatwebsite\Excel\Excel::ODS);
						break;
				}
			} else
				$departmentList = $departmentList->get();
		}
		return \App\Http\Resources\DepartmentResource::collection($departmentList);
	}

	// Save department
	public function save(Request $request)
	{
		$input = $request->all();
		if (isset($input["department"])) {
			$department = $input["department"];
			$objectToSave = [];
			$checkTitle = true;
			if ($department["action"] == "status") {
				$objectToSave["status"] = $department["status"];
				if ($department["status"] <= 0)
					$checkTitle = false;
			}
			if ($department["action"] == "details") {
				$rules = [
					'title' => 'required|string',

				];
				$validator = Validator::make($department, $rules);
				if ($validator->fails()) {
					return response()->json(["status" => -1, "messages" => array_merge(...array_values($validator->errors()->toArray()))]);
				}
				$objectToSave = $department;
				if ($department["id"] == 0) {
					if (!isset($objectToSave["created_by"]))
						$objectToSave["created_by"] = Auth::id();
				} else
					unset($objectToSave["created_by"]);
				if ($checkTitle && \App\Models\Department::where('title', $department["title"])->where("id", "!=", $department["id"])->where('status', 1)->count() > 0)
					return response()->json(["status" => -1, "messages" => ["Title has to be unique."]]);
			}
			\App\Models\Department::updateOrCreate(["id" => $department["id"]], $objectToSave);
			return response()->json(["status" => 1]);
		} else {
			return response()->json(["status" => -100, "messages" => ["Data for Department is missing."]]);
		}
	}
}
