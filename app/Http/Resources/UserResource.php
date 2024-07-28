<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class UserResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		$input = $request->all();
		$isUserAdmin = false;
		$canEdit = false;
		$canDeactivate = false;
		$canDelete = false;
		if (isset($input['current_user_id']) ) {
			if( $this->id == 1 ){
				$isUserAdmin = false;
				$canEdit = false;
				$canDeactivate = false;
				$canDelete = true;
			}
			else{
				$currentUser = User::find($input['current_user_id']);
				if ($currentUser->role_id == 1){
					$isUserAdmin = true;
					$canEdit = true;
					$canDeactivate = true;
				}
				else{
					$permissions = DB::table("role_object_mapping")
						->join('platform_object', 'role_object_mapping.platform_object_id', '=', 'platform_object.id')
						->select('role_object_mapping.view_records', 'role_object_mapping.can_add_edit', 'role_object_mapping.can_delete')
						->where("role_object_mapping.role_id", $currentUser->role_id)
						->where("platform_object.name", "User")
						->first();
					if($this->id == $input['current_user_id'])
						$canEdit = true;
					// User can Add/Edit. Can the user add/edit this record
					if($permissions != null && $permissions->can_add_edit)
						$canEdit = true;
					if($permissions != null && $permissions->can_delete)
						$canDeactivate = true;
				}
			}
		}
		// Set actions
		$actions = [
			'v' => ['title' => 'View', 'action' => 'viewObject', 'class' => 'btn-dark']
		];

		if( $canEdit )
			$actions['e'] = ['title' => 'Edit', 'action' => 'editObject', 'class' => 'btn-outline-dark'];
		$actions['p'] = ['title' => 'View Profile', 'action' => 'viewProfile', 'class' => 'btn-info'];
		if ($this->status == 1) {
			if ( $canDeactivate )
				$actions['d'] = ['title' => 'Deactivate', 'action' => 'toggleObjectStatus', 'class' => 'btn-secondary', 'additional_params' => [0]];
		}
		else {
			if ( $canDeactivate )
				$actions['r'] = ['title' => 'Activate', 'action' => 'toggleObjectStatus', 'class' => 'btn-success', 'additional_params' => [1]];
		}
		return [
			'id' => $this->id,
			'name' => $this->name,
			'email' => $this->email,
			'password' => $this->password,
			'department' => $this->department,
			'date_of_joining' => $this->date_of_joining, 
			'employee_code' => $this->employee_code,
			'reporting_to' => $this->reporting_to,
			'role_id' => $this->role_id,
			'designation' => $this->designation_id,
			'reporting_to_user' => $this->reporting_to_user,
			'role' => $this->role,
			'status' => $this->status,
			'current_user_admin' => ($isUserAdmin ? 1 : 0),
			'actions' => $actions
		];
	}
}
