<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model{
    protected $table = "role";
    protected $fillable = ['title', 'is_admin', 'status', 'created_by'];
    public $timestamps = true;

	//'pivot', 
	protected $hidden = ['created_at', 'updated_at' ];
	
	public function objects(): BelongsToMany{
		return $this->belongsToMany(PlatformObject::class, 'role_id')->withPivot('id', 'can_export', 'can_add_edit_duplicate', 'can_delete');
	}
}
