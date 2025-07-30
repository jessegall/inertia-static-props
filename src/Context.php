<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Http\Request;

class Context
{

    /**
     * Flag determining if a static props reload has been requested
     */
    protected bool|null $staticPropsReloadRequested = null;

    /**
     * Request a reload of the static props
     *
     * @return void
     */
    public function requestStaticPropsReload(): void
    {
        session()->flash('inertia.reload-static-props');
    }

    /**
     * Check if a reload is requested
     *
     * @return bool
     */
    public function isReloadRequested(): bool
    {
        return $this->staticPropsReloadRequested ??= session()->pull('inertia.reload-static-props', false);
    }

    /**
     * Check if the current request is a GET request
     *
     * @return bool
     */
    protected function isGetRequest(): bool
    {
        return request()->isMethod(Request::METHOD_GET);
    }

}