<?php

namespace App\Http\Resources;

use App\Models\PlatformObject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class RoleResource extends JsonResource
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
			$currentUser = User::find($input['current_user_id']);
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
			$actions = [];
			if( $isUserAdmin )
				$actions['v'] = [ 'title' => 'View', 'action' => 'viewObject', 'class' => 'btn-dark' ];
			if( $isUserAdmin )
				$actions['e'] = [ 'title' => 'Edit', 'action' => 'editObject', 'class' => 'btn-outline-dark' ];

			if( $isUserAdmin && $this->id > 1 ){
				$actions['m'] = [ 'title' => 'Map Objects', 'action' => 'mapObjects', 'class' => 'btn-info', 'additional_params' => [0] ];
			}
			if( $isUserAdmin && $this->id > 1 )
				$actions['d'] = [ 'title' => 'Deactivate', 'action' => 'toggleObjectStatus', 'class' => 'btn-secondary', 'additional_params' => [0] ];
		}
		else{
			if( $isUserAdmin )
				$actions['e'] = [ 'title' => 'Edit', 'action' => 'editObject', 'class' => 'btn-outline-dark' ];
			if( $isUserAdmin && $this->id > 1 )
				$actions['r'] = [ 'title' => 'Activate', 'action' => 'toggleObjectStatus', 'class' => 'btn-success', 'additional_params' => [1] ];
		};
		return [
			'id' => $this->id,
			'title' => $this->title,
			'status' => $this->status,
			'objects' => $this->objects,
			'current_user_admin' => ( $isUserAdmin ? 1 : 0 ),
			'actions' => $actions
		];
    }
}
