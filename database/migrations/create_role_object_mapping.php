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
        Schema::create('role_object_mapping', function (Blueprint $table) {
            $table->id();
			$table->integer('role_id');
			$table->integer('platform_object_id');
			$table->integer('view_records')->default(2)->comment("1=all employee records,2=own hierarchy");
			$table->boolean('can_add_edit');
			$table->boolean('can_delete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_object_mapping');
    }
};
