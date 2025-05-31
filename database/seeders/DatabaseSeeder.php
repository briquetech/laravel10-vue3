<?php

namespace Database\Seeders;

use App\Models\User;
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
        // User::factory(10)->create();

		// \App\Models\User::factory()->create([
		//     'name' => 'Test User',
		//     'email' => 'test@example.com',
		// ]);

		\App\Models\User::factory()->create([
			'name' => 'Administrator', 'email' => 'admin@example.com', 'password' => Hash::make('admin123'),
			'date_of_joining' => '2014-03-11',
			'department_id' => 1, 
			'employee_code' => 'BRQ000001', 'hierarchy_node_id' => '1.', 'role_id' => 1,
			'status' => true, 'created_by' => 1,
			'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")
		]);

		// boss 1
		\App\Models\User::factory()->create([
			'name' => 'Boss 1', 'email' => 'jayvpagnis@gmail.com', 'password' => Hash::make('brique123'),
			'date_of_joining' => '2014-03-11',
			'department_id' => 1, 
			'employee_code' => 'BRQ000002', 'hierarchy_node_id' => '1.2.', 'role_id' => 3,
			'status' => true, 'created_by' => 1,
			'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")
		]);

		// Employee 1
		\App\Models\User::factory()->create([
			'name' => 'Employee 1', 'email' => 'brique.technology@gmail.com', 'password' => Hash::make('brique123'),
			'date_of_joining' => '2014-03-11',
			'department_id' => 1, 
			'employee_code' => 'BRQ0010', 'hierarchy_node_id' => '1.2.3.', 'role_id' => 4,
			'status' => true, 'created_by' => 1,
			'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")
		]);
		// Employee 2
		\App\Models\User::factory()->create([
			'name' => 'Employee 2', 'email' => 'jay.pagnis@brique.in', 'password' => Hash::make('brique123'),
			'date_of_joining' => '2014-03-11',
			'department_id' => 1, 
			'employee_code' => 'BRQ0011', 'hierarchy_node_id' => '1.2.4.', 'role_id' => 4,
			'status' => true, 'created_by' => 1,
			'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")
		]);


		\App\Models\Department::create([ 'title' => 'Other', 'created_by' => 1, ]);

		\App\Models\Role::create([ 'title' => 'Administrator', 'created_by' => 1, ]);
		\App\Models\Role::create([ 'title' => 'Employee', 'created_by' => 1, ]);

		// --------------------------------------------------
		// Masters
		\App\Models\PlatformObject::create([
			'title' => 'Role', 'name' => 'Role', 'url' => '/role/',
			'phicon' => 'eyeglasses',
			'hierarchical' => false, 'for_admin_only' => true,
			'category' => 13, 'created_by' => 1,
		]);
		
		\App\Models\PlatformObject::create([
			'title' => 'Platform Objects', 'name' => 'PlatformObject', 'url' => '/platformobject/',
			'phicon' => 'article',
			'hierarchical' => false, 'for_admin_only' => true,
			'category' => 13, 'created_by' => 1,
		]);
		// --------------------------------------------------

		DB::table('role_object_mapping')->insert([
			'role_id' => 1,
			'platform_object_id' => 1,
			'can_add_edit' => true, 'can_delete' => true
		]);
		DB::table('role_object_mapping')->insert([
			'role_id' => 1,
			'platform_object_id' => 2,
			'can_add_edit' => true, 'can_delete' => true
		]);

	}
}
