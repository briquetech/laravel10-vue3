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
			$table->boolean("for_admin_only")->default(false);
			$table->boolean("hierarchical")->default(false);
			$table->integer("category")->default(1)->comment("1=Actions,2=others,3=Information,5=masters");
			$table->boolean("status")->default(true);
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
