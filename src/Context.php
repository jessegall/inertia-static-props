<?php

namespace JesseGall\InertiaStaticProps;

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
        session()->put('inertia.reload-static-props', true);
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

}