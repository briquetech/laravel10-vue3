<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Users extends Model{
    protected $table = "users";
    protected $fillable = ['name', 'email', 'password', 'department', 'employee_code', 'reporting_to', 'status'];
    public $timestamps = true;
	
	protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
	
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
	
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
