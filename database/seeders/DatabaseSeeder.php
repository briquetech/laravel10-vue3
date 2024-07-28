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
			'name' => 'Administrator', 'email' => 'admin@example.com', 'password' => Hash::make('admin123'),
			'date_of_joining' => '2014-03-11',
			'department_id' => 1, 'designation_id' => 1,
			'employee_code' => 'BRQ000001', 'reporting_to' => 0, 'hierarchy_node_id' => '1.', 'role_id' => 1,
			'status' => true, 'created_by' => 1,
			'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")
		]);

		// boss 1
		\App\Models\User::factory()->create([
			'name' => 'Boss 1', 'email' => 'jayvpagnis@gmail.com', 'password' => Hash::make('brique123'),
			'date_of_joining' => '2014-03-11',
			'department_id' => 1, 'designation_id' => 1,
			'employee_code' => 'BRQ000002', 'reporting_to' => 0, 'hierarchy_node_id' => '1.2.', 'role_id' => 3,
			'status' => true, 'created_by' => 1,
			'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")
		]);

		// Employee 1
		\App\Models\User::factory()->create([
			'name' => 'Employee 1', 'email' => 'brique.technology@gmail.com', 'password' => Hash::make('brique123'),
			'date_of_joining' => '2014-03-11',
			'department_id' => 1, 'designation_id' => 1,
			'employee_code' => 'BRQ0010', 'reporting_to' => 2, 'hierarchy_node_id' => '1.2.3.', 'role_id' => 4,
			'status' => true, 'created_by' => 1,
			'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")
		]);
		// Employee 2
		\App\Models\User::factory()->create([
			'name' => 'Employee 2', 'email' => 'jay.pagnis@brique.in', 'password' => Hash::make('brique123'),
			'date_of_joining' => '2014-03-11',
			'department_id' => 1, 'designation_id' => 1,
			'employee_code' => 'BRQ0011', 'reporting_to' => 2, 'hierarchy_node_id' => '1.2.4.', 'role_id' => 4,
			'status' => true, 'created_by' => 1,
			'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")
		]);


		\App\Models\Department::create([ 'title' => 'Other', 'created_by' => 1, ]);
		\App\Models\Designation::create([ 'title' => 'Employee', 'created_by' => 1, ]);

		\App\Models\Role::create([ 'title' => 'Administrator', 'created_by' => 1, ]);
		\App\Models\Role::create([ 'title' => 'HR', 'created_by' => 1, ]);
		\App\Models\Role::create([ 'title' => 'Management', 'created_by' => 1, ]);
		\App\Models\Role::create([ 'title' => 'Employee', 'created_by' => 1, ]);
		\App\Models\Role::create([ 'title' => 'Accounts', 'created_by' => 1, ]);

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
			'platform_object_id' => 1
		]);
		DB::table('role_object_mapping')->insert([
			'role_id' => 1,
			'platform_object_id' => 2
		]);

		// Notifications
		\App\Models\Settings::insert([
			["parameter_name" => "license_to_user", "parameter_value" => "Jay Pagnis"],
			["parameter_name" => "license_to_user_email", "parameter_value" => "jayvpagnis@gmail.com"],
			["parameter_name" => "license_start_from", "parameter_value" => "2024-04-01"],
			["parameter_name" => "license_ends_on", "parameter_value" => "2025-03-31"],
			["parameter_name" => "license_users", "parameter_value" => "5"],
			["parameter_name" => "company_name", "parameter_value" => "BRIQUE Technology Solutions and Consulting"],
			["parameter_name" => "company_logo", "parameter_value" => ""],
			["parameter_name" => "company_email", "parameter_value" => "info@brique.in"],
			["parameter_name" => "company_phone", "parameter_value" => "09004078268"],
			["parameter_name" => "company_website", "parameter_value" => "https://brique.in"],
			["parameter_name" => "smtp_host", "parameter_value" => "smtp.gmail.com"],
			["parameter_name" => "smtp_port", "parameter_value" => "587"],
			["parameter_name" => "smtp_username", "parameter_value" => "brique.solutions@gmail.com"],
			["parameter_name" => "smtp_password", "parameter_value" => "ekomzpjsopasvbpt"],
			["parameter_name" => "smtp_from_email_address", "parameter_value" => "no-reply@brique.in"],
			["parameter_name" => "smtp_from_name", "parameter_value" => "BRIQUE HROps No Reply"],
			["parameter_name" => "smtp_encryption", "parameter_value" => "tls"],
		]);
	}
}
