<?php

namespace JesseGall\InertiaStaticProps;

use Inertia\Response;
use Inertia\ResponseFactory;
use Inertia\Support\Header;

/**
 * @implements Decorator<ResponseFactory>
 */
class ResponseFactoryDecorator extends ResponseFactory implements Decorator
{
    /**
     * @use Decorates<ResponseFactory>
     */
    use Decorates;

    /**
     * To ensure that custom ResponseFactory implementations will continue to work,
     * we delegate all properties to the given ResponseFactory.
     *
     * @param ResponseFactory $delegate
     */
    public function __construct(ResponseFactory $delegate)
    {
        $this->delegateTo($delegate);
    }

    /**
     * We override the render method to resolve static props when necessary.
     *
     * @param string $component The component to render
     * @param mixed $props The props to pass to the component
     * @return Response The response
     */
    public function render(string $component, $props = []): Response
    {
        // We render the component using the delegate's render method,
        $response = $this->delegate->render($component, $props);

        $decorator = new ResponseDecorator($response);

        if ($this->shouldLoadStaticProps()) {
            $decorator->loadStaticProps();
        }

        return $decorator->delegate;
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