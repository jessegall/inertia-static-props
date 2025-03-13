<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Inertia\Inertia;
use Inertia\ResponseFactory;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->extend(ResponseFactory::class, fn($factory) => new ResponseFactoryDecorator($factory));
        $this->app->singleton(StaticPropsReloader::class);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Inertia::macro('static', fn(callable $value) => new StaticProp($value));
        Inertia::macro('staticProp', fn(callable $value) => new StaticProp($value)); // Alias for static
        Inertia::macro('reloadStaticProps', $this->app->make(StaticPropsReloader::class));
    }

}
