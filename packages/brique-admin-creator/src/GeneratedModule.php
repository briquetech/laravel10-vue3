<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedModule extends Model
{
    use HasFactory;
    protected $table = "generated_modules";
	protected $fillable = ['code', 'object'];
    public $timestamps = true;
}
