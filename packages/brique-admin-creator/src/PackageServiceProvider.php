<?php
namespace BriqueAdminCreator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Console\AboutCommand;

class PackageServiceProvider extends ServiceProvider
{
    public function boot(): void{
		AboutCommand::add('Admin Creator Package by @briquetech', fn () => ['Version' => '1.0.0']);

		$this->publishes([
			__DIR__.'/database/migrations' => database_path('migrations'),
		], 'migrations');

		$this->loadRoutesFrom(__DIR__.'/routes/web.php');
		$this->loadRoutesFrom(__DIR__.'/routes/api.php');

		$this->loadViewsFrom(__DIR__.'/resources/views/', 'brique-admin-creator');
	}
 
    public function register(){

    }
}