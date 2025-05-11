<?php

namespace JesseGall\InertiaStaticProps;

use Inertia\Response;
use Inertia\Support\Header;

/**
 * @implements Decorator<Response>
 */
class ResponseDecorator extends Response implements Decorator
{
    /**
     * @use Decorates<Response>
     */
    use Decorates;

    /**
     * The static properties that were present on the response.
     *
     * @var array
     */
    private array $staticProps;

    /**
     * Delegates all properties to the given Response, allowing
     * this class to effectively use "$this" as if it were the
     * delegate instance. This ensures custom Response
     * implementations continue to work when wrapped.
     */
    public function __construct(Response $delegate)
    {
        parent::__construct(
            $delegate->component,
            $delegate->props,
            $delegate->rootView,
            $delegate->version,
            $delegate->encryptHistory
        );

        $this->delegateTo($delegate);

        $this->staticProps = $this->resolveStaticProps();
    }

    /**
     * Add static props to the response.
     *
     * @return void
     */
    public function loadStaticProps(): void
    {
        $loaded = [];

        foreach ($this->staticProps as $key => $prop) {
            $this->props[$key] = $prop->asClosure();

            $loaded[] = $key;
        }

        // Store a list of static props for the client to use.
        $this->props['staticProps'] = $loaded;
    }

    /**
     * Override the toResponse method to load static props when necessary.
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse|mixed|\Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $props = $this->props;

        if ($this->shouldLoadStaticProps()) {
            $this->loadStaticProps();
        } else {
            $this->removeStaticProps();
        }

        $response = $this->delegate->toResponse($request);

        $this->props = $props;

        return $response;
    }

    /**
     * Resolve static props from the response.
     *
     * @return StaticProp[]
     */
    protected function resolveStaticProps(): array
    {
        return array_filter($this->props, fn($prop) => $prop instanceof StaticProp);
    }

    /**
     * Remove static props from the response.
     *
     * @return void
     */
    protected function removeStaticProps(): void
    {
        $this->props = array_filter($this->props, fn($prop) => ! $prop instanceof StaticProp);
    }


    /**
     * Determine if static props should be loaded
     *
     * @return bool
     */
    protected function shouldLoadStaticProps(): bool
    {
        return $this->isInitialRequest() || $this->isReloadRequested();
    }

    /**
     * Check if this is the initial request
     *
     * @return bool
     */
    protected function isInitialRequest(): bool
    {
        return ! request()->header(Header::INERTIA);
    }

    /**
     * Check if a reload is requested
     *
     * @return bool
     */
    protected function isReloadRequested(): bool
    {
        return app(Context::class)->isReloadRequested();
    }

}