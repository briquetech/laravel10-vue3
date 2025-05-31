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
		Schema::create('company', function (Blueprint $table) {
            $table->id();
			$table->string("name");
			$table->string("cin")->nullable();
			$table->string("pan_no")->nullable();
			$table->string("gst_no")->nullable();
			$table->string("header_url")->nullable();
			$table->string("signature_url")->nullable();
			$table->boolean('status')->default(true);
			$table->integer("created_by")->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('company', function(Blueprint $table){
            $table->dropSoftDeletes();
        });
	}
};
