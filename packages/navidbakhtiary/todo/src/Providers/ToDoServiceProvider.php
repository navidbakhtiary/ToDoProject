<?php

namespace NavidBakhtiary\ToDo\Providers;

use Illuminate\Support\ServiceProvider;

class ToDoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(dirname(__DIR__, 1) . '/Database/Migrations');
        $this->loadFactoriesFrom(dirname(__DIR__, 1) . '/Database/Factories');
        
        $this->loadRoutesFrom(dirname(__DIR__, 1) . '/Routes/routes.php');
    }
}
