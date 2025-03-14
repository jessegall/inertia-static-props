<?php

namespace Tests\Feature;

use Illuminate\Routing\Router;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Inertia;
use Inertia\Response;
use Inertia\ResponseFactory;
use Inertia\Support\Header;
use Inertia\Testing\AssertableInertia as Assert;
use JesseGall\InertiaStaticProps\Context;
use JesseGall\InertiaStaticProps\StaticProp;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

class InertiaStaticPropsTest extends TestCase
{
    use WithWorkbench;

    protected function defineRoutes($router)
    {
        $router->get('/test', fn() => Inertia::render('TestComponent'));
    }

    public function test_StaticPropsAreIncludedInInitialPageLoad()
    {
        Inertia::share([
            'staticPropOne' => new StaticProp(fn() => 'one'),
            'staticPropTwo' => new StaticProp(fn() => 'two'),
            'nonStaticProp' => 'value',
        ]);

        $this
            ->withoutExceptionHandling()
            ->get('/test')
            ->assertInertia(fn(Assert $page) => $page
                ->where('staticPropOne', 'one')
                ->where('staticPropTwo', 'two')
                ->where('nonStaticProp', 'value')
                ->where('staticProps', ['staticPropOne', 'staticPropTwo'])
                ->etc()
            );
    }

    public function test_StaticPropsAreIncludedInInitialPageLoad_WhenRenderedWithStaticProps()
    {
        $this->app->make(Router::class)->get('/test-with-render-static-props', function () {
            return Inertia::render('TestComponent', [
                'staticPropOne' => new StaticProp(fn() => 'one'),
                'staticPropTwo' => new StaticProp(fn() => 'two'),
                'nonStaticProp' => 'value',
            ]);
        });

        $this
            ->withoutExceptionHandling()
            ->get('/test-with-render-static-props')
            ->assertInertia(fn(Assert $page) => $page
                ->where('staticPropOne', 'one')
                ->where('staticPropTwo', 'two')
                ->where('nonStaticProp', 'value')
                ->where('staticProps', ['staticPropOne', 'staticPropTwo'])
                ->etc()
            );
    }

    public function test_StaticPropsAreIncludedAfterInitialPageLoad_WhenRenderingWithStaticProps_AndStaticPropsAreReloaded()
    {
        $this->app->make(Router::class)->get('/test-with-render-static-props-and-reload', function () {
            Inertia::reloadStaticProps();

            return Inertia::render('TestComponent', [
                'staticPropOne' => new StaticProp(fn() => 'one'),
                'staticPropTwo' => new StaticProp(fn() => 'two'),
                'nonStaticProp' => 'value',
            ]);
        });

        $this
            ->withoutExceptionHandling()
            ->get('/test-with-render-static-props-and-reload', [
                Header::INERTIA => true,
            ])
            ->assertJson(fn(AssertableJson $json) => $json
                ->has('props', fn(AssertableJson $props) => $props
                    ->where('staticPropOne', 'one')
                    ->where('staticPropTwo', 'two')
                    ->where('staticProps', ['staticPropOne', 'staticPropTwo'])
                    ->etc()
                )
                ->etc()
            );
    }

    public function test_StaticPropsAreIncludedAfterInitialPageLoad_WhenRenderingWithStaticProps_AndStaticPropsAreReloaded_UsingResponseMacro()
    {
        $this->app->make(Router::class)->get('/test-with-render-static-props-and-reload', function () {
            return Inertia::render('TestComponent', [
                'staticPropOne' => new StaticProp(fn() => 'one'),
                'staticPropTwo' => new StaticProp(fn() => 'two'),
                'nonStaticProp' => 'value',
            ])->withStaticProps();
        });

        $this
            ->withoutExceptionHandling()
            ->get('/test-with-render-static-props-and-reload', [
                Header::INERTIA => true,
            ])
            ->assertJson(fn(AssertableJson $json) => $json
                ->has('props', fn(AssertableJson $props) => $props
                    ->where('staticPropOne', 'one')
                    ->where('staticPropTwo', 'two')
                    ->where('staticProps', ['staticPropOne', 'staticPropTwo'])
                    ->etc()
                )
                ->etc()
            );
    }

    public function test_StaticPropsCanBeSharedUsingMacro()
    {
        Inertia::share([
            'staticPropOne' => Inertia::static(fn() => 'one'),
            'staticPropTwo' => Inertia::static(fn() => 'two'),
        ]);

        $this
            ->withoutExceptionHandling()
            ->get('/test')
            ->assertInertia(fn(Assert $page) => $page
                ->where('staticPropOne', 'one')
                ->where('staticPropTwo', 'two')
                ->where('staticProps', ['staticPropOne', 'staticPropTwo'])
                ->etc()
            );
    }

    public function test_StaticPropsAreOmittedFromSubsequentVisits()
    {
        Inertia::share([
            'staticPropOne' => new StaticProp(fn() => 'one'),
            'staticPropTwo' => new StaticProp(fn() => 'two'),
            'nonStaticProp' => 'value',
        ]);

        $this
            ->withoutExceptionHandling()
            ->get('/test', [
                Header::INERTIA => true,
            ])
            ->assertJson(fn(AssertableJson $json) => $json
                ->has('props', fn(AssertableJson $props) => $props
                    ->missing('staticPropOne')
                    ->missing('staticPropTwo')
                    ->missing('staticProps')
                    ->etc()
                )
                ->etc()
            );
    }

    public function test_StaticPropsAreNotResolved_WhenOmitted()
    {
        $triggered = false;

        Inertia::share([
            'staticProp' => new StaticProp(function () use (&$triggered) {
                $triggered = true;
                return 'one';
            }),
        ]);

        $this
            ->withoutExceptionHandling()
            ->get('/test', [Header::INERTIA => true]);

        $this->assertFalse($triggered);
    }

    public function test_StaticPropsAreIncludedInSubsequentVisits_WhenStaticPropsAreReloaded()
    {
        Inertia::share([
            'staticPropOne' => new StaticProp(fn() => 'one'),
            'staticPropTwo' => new StaticProp(fn() => 'two'),
            'nonStaticProp' => 'value',
        ]);

        Inertia::reloadStaticProps();

        $this
            ->withoutExceptionHandling()
            ->get('/test', [
                Header::INERTIA => true,
            ])
            ->assertJson(fn(AssertableJson $json) => $json
                ->has('props', fn(AssertableJson $props) => $props
                    ->where('staticPropOne', 'one')
                    ->where('staticPropTwo', 'two')
                    ->where('staticProps', ['staticPropOne', 'staticPropTwo'])
                    ->etc()
                )
                ->etc()
            );
    }

    public function test_StaticPropsAreIncludedInSubsequentVisits_WhenStaticPropsAreReloaded_DuringNonGetRequest()
    {
        $this->app->make(Router::class)->post('/test', function () {
            Inertia::reloadStaticProps();
            return redirect('/test');
        });

        Inertia::share([
            'staticPropOne' => new StaticProp(fn() => 'one'),
            'staticPropTwo' => new StaticProp(fn() => 'two'),
            'nonStaticProp' => 'value',
        ]);

        $this
            ->withoutExceptionHandling()
            ->post('/test')
            ->assertRedirect('/test');

        $this->assertTrue(session()->has('inertia.reload-static-props'));

        $this
            ->withoutExceptionHandling()
            ->get('/test', [
                Header::INERTIA => true,
            ])
            ->assertJson(fn(AssertableJson $json) => $json
                ->has('props', fn(AssertableJson $props) => $props
                    ->where('staticPropOne', 'one')
                    ->where('staticPropTwo', 'two')
                    ->where('staticProps', ['staticPropOne', 'staticPropTwo'])
                    ->etc()
                )
                ->etc()
            );
    }

    public function test_SupportsCustomResponses_WhenWrapped()
    {
        $currentFactory = Inertia::getFacadeRoot();

        $customFactory = new class($currentFactory) extends ResponseFactory {

            public function __construct(
                protected ResponseFactory $delegate
            ) {}

            public function render(string $component, $props = []): Response
            {
                return $this->delegate->render($component, [
                    'custom_injected_prop' => 'value',
                    ...$this->sharedProps,
                    ...$props,
                ]);
            }

        };

        Inertia::swap($customFactory);

        Inertia::share([
            'staticPropOne' => new StaticProp(fn() => 'one'),
            'staticPropTwo' => new StaticProp(fn() => 'two'),
        ]);

        $this->withoutExceptionHandling()
            ->get('/test')
            ->assertInertia(fn(Assert $page) => $page
                ->where('staticPropOne', 'one')
                ->where('staticPropTwo', 'two')
                ->where('custom_injected_prop', 'value')
                ->etc()
            );
    }

    public function test_SupportsCustomResponses_WhenWrapping()
    {
        $currentFactory = Inertia::getFacadeRoot();

        $customFactory = new class extends ResponseFactory {

            public function render(string $component, $props = []): Response
            {
                return parent::render($component, [
                    'custom_injected_prop' => 'value',
                    ...$props
                ]);
            }

        };

        $currentFactory->decorate($customFactory);

        Inertia::share([
            'staticPropOne' => new StaticProp(fn() => 'one'),
            'staticPropTwo' => new StaticProp(fn() => 'two'),
        ]);

        $this->withoutExceptionHandling()
            ->get('/test')
            ->assertInertia(fn(Assert $page) => $page
                ->where('staticPropOne', 'one')
                ->where('staticPropTwo', 'two')
                ->where('custom_injected_prop', 'value')
                ->etc()
            );
    }

}