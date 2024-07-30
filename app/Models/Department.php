<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Department extends Model{
    protected $table = "department";
    protected $fillable = ['title', 'created_by', 'status'];
    public $timestamps = true;
	
	public function creator(){
	return $this->belongsTo('App\Models\User', 'created_by', 'id');
}


}