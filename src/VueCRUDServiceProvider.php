<?php

namespace Datalytix\VueCRUD;

use Illuminate\Support\ServiceProvider;

class VueCRUDServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/resources/views/', 'vue-crud');

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/packages/vue-crud'),
        ], 'vuecrud-views');
        $this->publishes([
            __DIR__.'/resources/js' => resource_path('js'),
        ], 'vuecrud-scripts');
        $this->publishes([
            __DIR__.'/Config' => base_path('config'),
        ], 'vuecrud-config');

        $this->commands([
            \Datalytix\VueCRUD\Commands\VueCRUDGenerate::class,
            \Datalytix\VueCRUD\Commands\CssClasses::class,
        ]);
    }
}
