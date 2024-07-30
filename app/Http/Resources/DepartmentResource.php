<?php

namespace App\Http\Resources;

use App\Models\PlatformObject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array{
		$input = $request->all();
		$isUserAdmin = false;
		$canEdit = false;
		$canDelete = false;
		if( isset($input['current_user_id']) && $input['current_user_id'] > 0 ){
			$currentUser = \App\Models\User::find($input['current_user_id']);
			if( $currentUser->role_id == 1 )
				$isUserAdmin = true;
			else{
				$platformObject = PlatformObject::where('title', 'Role')->first();
				$permissions = DB::table('role_object_mapping')
					->where('role_id', $currentUser->role_id)
					->where('platform_object_id', $platformObject->id)
					->first();
				if( $permissions !== null ){
					$canEdit .= ($permissions->can_add_edit ? true: false);
					$canDelete .= ($permissions->can_delete ? true: false);
				}
			}
		}
		// Set actions
		$actions = [];
		if( $this->status == 1 ){
			$actions = [
				'v' => [ 'title' => 'View', 'action' => 'viewObject', 'class' => 'btn-dark' ]
			];
			if( $canEdit )
				$actions['e'] = [ 'title' => 'Edit', 'action' => 'editObject', 'class' => 'btn-outline-dark' ];

			if( $isUserAdmin || $canDelete )
				$actions['d'] = [ 'title' => 'Deactivate', 'action' => 'toggleObjectStatus', 'class' => 'btn-secondary', 'additional_params' => [0] ];
		}
		else{
			if( $isUserAdmin || $canEdit )
				$actions['e'] = [ 'title' => 'Edit', 'action' => 'editObject', 'class' => 'btn-outline-dark' ];
			if( $isUserAdmin || $canDelete )
				$actions['r'] = [ 'title' => 'Activate', 'action' => 'toggleObjectStatus', 'class' => 'btn-success', 'additional_params' => [1] ];
		};
		if( strlen($this->title) > 4 ){
			$actions['r'] = [ 'title' => 'Hatao', 'action' => 'toggleObjectStatus', 'class' => 'btn-danger', 'additional_params' => [1] ];
		}
		return [
			'id' => $this->id,
			'title' => $this->title,
			'created_by' => $this->created_by,
			'creator' => $this->creator,
			'status' => $this->status,
			'created_by' => $this->created_by,
			'current_user_admin' => ( $isUserAdmin ? 1 : 0 ),
			'actions' => $actions
		];
    }
}
