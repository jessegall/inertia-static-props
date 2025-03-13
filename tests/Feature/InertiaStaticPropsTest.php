<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Inertia;
use Inertia\Response;
use Inertia\Support\Header;
use Inertia\Testing\AssertableInertia as Assert;
use JesseGall\InertiaStaticProps\Delegates;
use JesseGall\InertiaStaticProps\DelegatorContract;
use JesseGall\InertiaStaticProps\ResponseDecorator;
use JesseGall\InertiaStaticProps\StaticProp;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

class InertiaStaticPropsTest extends TestCase
{
    use WithWorkbench;

    protected function defineRoutes($router)
    {
        $router->get('/test', fn() => Inertia::render('TestComponent'));

        $router->get('/test-with-render-static-props', function () {
            return Inertia::render('TestComponent', [
                'staticPropOne' => new StaticProp(fn() => 'one'),
                'staticPropTwo' => new StaticProp(fn() => 'two'),
                'nonStaticProp' => 'value',
            ]);
        });

        $router->post('/test', function () {
            Inertia::reloadStaticProps();
            return redirect('/test');
        });
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
                ->where('staticProps', ['staticPropOne', 'staticPropTwo'])
                ->etc()
            );
    }

    public function test_StaticPropsAreIncludedInInitialPageLoad_WhenRenderedWithStaticProps()
    {
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

}