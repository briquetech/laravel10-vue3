<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

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
		if (isset($input['current_user_id']) && $input['current_user_id'] > 0) {
			$currentUser = User::find($input['current_user_id']);
			if ($currentUser->role_id == 1)
				$isUserAdmin = true;
		}
		// Set actions
		$actions = [];
		if ($this->status == 1) {
			$actions = [
				'v' => ['title' => 'View', 'action' => 'viewObject', 'class' => 'btn-dark']
			];
			if ($isUserAdmin == 1)
				$actions['d'] = ['title' => 'Deactivate', 'action' => 'toggleObjectStatus', 'class' => 'btn-secondary', 'additional_params' => [0]];
		} else {
			if ($isUserAdmin == 1) {
				$actions = [
					'e' => ['title' => 'Edit', 'action' => 'editObject', 'class' => 'btn-dark'],
					'r' => ['title' => 'Activate', 'action' => 'toggleObjectStatus', 'class' => 'btn-success', 'additional_params' => [1]]
				];
			}
		};
		return [
			'id' => $this->id,
			'name' => $this->name,
			'email' => $this->email,
			'password' => $this->password,
			'department' => $this->department,
			'employee_code' => $this->employee_code,
			'reporting_to' => $this->reporting_to,
			'role_id' => $this->role_id,
			'reporting_to_user' => $this->reporting_to_user,
			'role' => $this->role,
			'status' => $this->status,
			'current_user_admin' => ($isUserAdmin ? 1 : 0),
			'actions' => $actions
		];
	}
}
