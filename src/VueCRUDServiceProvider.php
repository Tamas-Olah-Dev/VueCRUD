<?php

namespace OlahTamas\VueCRUD;

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
            __DIR__.'/resources/views' => resource_path('views/vendor/vue-crud'),
            __DIR__.'/resources/js' => resource_path('js'),

        ]);

        $this->commands([
            \OlahTamas\VueCRUD\Commands\VueCRUDGenerate::class,
        ]);
    }
}
