<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\ResponseFactory;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function register(): void
    {
        $this->app->extend(ResponseFactory::class, fn($factory) => new ResponseFactoryDecorator($factory));
    }

    public function boot(): void
    {
        $this->registerMacro();
    }

    public function registerMacro(): void
    {
        Inertia::macro('reloadStaticProps', function () {
            if (request()->isMethod(Request::METHOD_GET)) {
                ResponseFactoryDecorator::loadStaticProps();
            } else {
                session()->flash('inertia.reload-static-props');
            }
        });
    }
}
