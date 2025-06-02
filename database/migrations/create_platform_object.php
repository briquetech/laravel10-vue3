<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('platform_object', function (Blueprint $table) {
			$table->id();
			$table->string("title")->unique();
			$table->string("name")->unique();
			$table->string("url")->unique();
			$table->string("phicon");
			$table->integer("role_id");
			$table->integer("can_export")->comment("1=can export, 0=can't export");
			$table->integer("can_add_edit_duplicate")->comment("1=can A-E-D, 0=can't can A-E-D");
			$table->integer("can_delete")->comment("1=can delete, 0=can't delete");
			$table->integer("created_by");
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('platform_object');
	}
};
