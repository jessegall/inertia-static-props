<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Inertia\ResponseFactory;

class ServiceProvider extends BaseServiceProvider
{

    public function register(): void
    {
        $this->registerFactoryDecorator();
        $this->registerContext();
    }

    public function boot(): void
    {
        $this->registerInertiaMacros();
        $this->registerResponseMacros();
    }

    private function registerFactoryDecorator(): void
    {
        $this->app->booted(function ($app) {
            $factory = $app->make(ResponseFactory::class);

            $decorator = new ResponseFactoryDecorator($factory);

            Inertia::swap($decorator);
        });
    }

    private function registerContext(): void
    {
        $this->app->singleton(Context::class);
    }

    private function registerInertiaMacros(): void
    {
        $context = $this->context();

        Inertia::macro('static', fn(callable $value) => new StaticProp($value));
        Inertia::macro('staticProp', fn(callable $value) => new StaticProp($value)); // Alias for static
        Inertia::macro('reloadStaticProps', function () {
            app(Context::class)->requestStaticPropsReload();
            return $this;
        });
    }

    private function registerResponseMacros(): void
    {
        InertiaResponse::macro('withStaticProps', function () {
            app(Context::class)->requestStaticPropsReload();
            return $this;
        });

        Response::macro('withStaticProps', function () {
            app(Context::class)->requestStaticPropsReload();
            return $this;
        });
    }

    private function context(): Context
    {
        return $this->app->make(Context::class);
    }

}
