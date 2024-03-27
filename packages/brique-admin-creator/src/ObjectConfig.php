<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjectConfig extends Model
{
    use HasFactory;

    protected $table = "object_config";
	protected $fillable = ['db_name', 'table_name', 'object_settings'];
    public $timestamps = true;
}
