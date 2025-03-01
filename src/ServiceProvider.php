<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\ResponseFactory;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function register()
    {
        $this->app->extend(ResponseFactory::class, fn($factory) => new ResponseFactoryDecorator($factory));
    }

    public function boot(): void
    {
        $this->registerMacro();
    }

    public function registerMacro(): void
    {
        /** @var ResponseFactoryDecorator $decorator */
        $decorator = $this->app->make(ResponseFactory::class);

        Inertia::macro('reloadStaticProps', function () use ($decorator) {
            if (request()->isMethod(Request::METHOD_GET)) {
                $decorator->loadStaticProps();
            } else {
                session()->flash('inertia.reload-static-props');
            }
        });
    }
}
