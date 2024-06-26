<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformObject extends Model{
    protected $table = "platform_object";
    protected $fillable = ['title', 'name', 'url', 'phicons', 'for_admin_only', 'hierarchical', 'category', 'status', 'created_by'];
    public $timestamps = true;
	
	protected $casts = [
        'for_admin_only' => 'boolean',
        'hierarchical' => 'boolean',
        'status' => 'boolean',
    ];
}