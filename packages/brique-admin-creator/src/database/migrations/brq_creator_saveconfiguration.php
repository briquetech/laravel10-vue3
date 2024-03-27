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
		Schema::create('object_config', function(Blueprint $table)
		{
			$table->id();
			$table->string('db_name');
			$table->string('table_name');
			$table->longText('object_settings');
			$table->timestamps();
			$table->index(['db_name', 'table_name']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::drop('object_config');
	}
};
