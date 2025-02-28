<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Inertia;
use Inertia\Support\Header;
use Inertia\Testing\AssertableInertia as Assert;
use JesseGall\InertiaStaticProps\StaticProp;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

class InertiaStaticPropsTest extends TestCase
{
    use WithWorkbench;

    protected function defineRoutes($router)
    {
        $router->get('/test', fn() => Inertia::render('TestComponent'));

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

        $this->get('/test')
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

        $this->get('/test', [Header::INERTIA => true]);

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
            ->post('/test')
            ->assertRedirect('/test');

        $this->assertTrue(session()->has('inertia.reload-static-props'));

        $this
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