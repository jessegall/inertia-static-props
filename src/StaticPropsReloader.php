<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Http\Request;

class StaticPropsReloader
{

    /**
     * Flag determining if static props should be reloaded
     */
    protected bool $reloadStaticProps = false;

    /**
     * Handle the static props reload
     *
     * @return void
     */
    public function __invoke(): void
    {
        if ($this->isGetRequest()) {
            $this->reloadStaticProps = true;
        } else {
            session()->flash('inertia.reload-static-props');
        }
    }

    /**
     * Check if a reload is requested
     *
     * @return bool
     */
    public function isReloadRequested(): bool
    {
        return $this->reloadStaticProps || session()->pull('inertia.reload-static-props', false);
    }

    /**
     * Check if the current request is a GET request
     *
     * @return bool
     */
    private function isGetRequest(): bool
    {
        return request()->isMethod(Request::METHOD_GET);
    }

}