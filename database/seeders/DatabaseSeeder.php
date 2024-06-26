<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 */
	public function run(): void
	{
		// \App\Models\User::factory(10)->create();

		// \App\Models\User::factory()->create([
		//     'name' => 'Test User',
		//     'email' => 'test@example.com',
		// ]);

		\App\Models\User::factory()->create([
			'name' => 'Administrator',
			'email' => 'admin@gmail.com',
			'password' => Hash::make('admin@123'),
			'department' => 'Administration',
			'employee_code' => 'BRQ00001',
			'reporting_to' => 0,
			'role_id' => 1,
			'status' => true,
			'created_at' => date("Y-m-d H:i:s"),
			'updated_at' => date("Y-m-d H:i:s")
		]);

		\App\Models\Role::create([
			'title' => 'Administrator'
		]);

		\App\Models\PlatformObject::create([
			'title' => 'Users',
			'name' => 'User',
			'url' => '/user/',
			'phicon' => 'users',
			'hierarchical' => true,
			'for_admin_only' => true,
			'category' => 2,
			'created_by' => 1,
		]);
		\App\Models\PlatformObject::create([
			'title' => 'Role',
			'name' => 'Role',
			'url' => '/role/',
			'phicon' => 'eyeglasses',
			'hierarchical' => false,
			'for_admin_only' => true,
			'category' => 10,
			'created_by' => 1,
		]);

		DB::table('role_object_mapping')->insert([
			'role_id' => 1,
			'platform_object_id' => 1
		]);
		DB::table('role_object_mapping')->insert([
			'role_id' => 1,
			'platform_object_id' => 2
		]);
	}
}
