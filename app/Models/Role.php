<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model{
    protected $table = "role";
    protected $fillable = ['title', 'status', 'created_by'];
    public $timestamps = true;

	//'pivot', 
	protected $hidden = ['created_at', 'updated_at' ];
	
	public function objects(): BelongsToMany{
		return $this->belongsToMany(PlatformObject::class, 'role_object_mapping', 'role_id', 'platform_object_id')->withPivot('id', 'view_records', 'can_add_edit', 'can_delete')->orderByPivot('platform_object_id');
	}
}
