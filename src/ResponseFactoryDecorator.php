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

        return new ResponseDecorator($response);
    }

}