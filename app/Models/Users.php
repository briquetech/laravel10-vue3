<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Users extends Model{
    protected $table = "users";
    protected $fillable = ['name', 'email', 'password', 'department', 'employee_code', 'status'];
    public $timestamps = true;
	
	
	protected function actions(): Attribute{
		return new Attribute(
			get: function () {
				$actions = [];
				if( $this->status == 1 ){
					$actions = [
						'v' => [ 'title' => 'View', 'action' => 'viewObject', 'class' => 'btn-dark' ], 
						'd' => [ 'title' => 'Deactivate', 'action' => 'toggleObjectStatus', 'class' => 'btn-secondary', 'additional_params' => [0] ]
					];
				}
				else{
					$actions = [
						'e' => [ 'title' => 'Edit', 'action' => 'editObject', 'class' => 'btn-dark' ], 
						'r' => [ 'title' => 'Activate', 'action' => 'toggleObjectStatus', 'class' => 'btn-success', 'additional_params' => [1] ]
					];
				}
				return $actions;
			}
		);
	}
}