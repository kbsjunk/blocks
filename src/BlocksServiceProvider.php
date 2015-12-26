<?php

namespace Kitbs\Blocks;

use Illuminate\Support\ServiceProvider;

class BlocksServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
      $this->bootViews();

      
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerMigrationCommand();
        $this->registerLangCommand();
    }
  
    private function bootViews()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/blocks', 'blocks');

        $this->publishes([
          __DIR__.'/../resources/blocks' => resource_path('blocks'),
        ]);
    }

    private function registerLangCommand()
    {
        $this->app->singleton('command.blocks.lang', function ($app) {
            return $app['Kitbs\Blocks\Commands\BlocksLangCommand'];
        });
        
        $this->commands('command.blocks.lang');
    }

    private function registerMigrationCommand()
    {
        $this->app->singleton('command.blocks.migration', function ($app) {
            return $app['Kitbs\Blocks\Commands\BlocksMigrationCommand'];
        });
        
        $this->commands('command.blocks.migration');
    }
}
