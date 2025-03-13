<?php

namespace JesseGall\InertiaStaticProps;

use Inertia\Response;
use Inertia\ResponseFactory;
use Inertia\Support\Header;
use Override;

/**
 * @implements Decorator<ResponseFactory>
 */
class ResponseFactoryDecorator extends ResponseFactory implements Decorator
{
    use Delegates;

    public function __construct(
        public readonly object $delegate
    )
    {
        $this->initializePropertyDelegation();
    }

    /**
     * Render a component with props and wrap in ResponseDecorator
     *
     * @param string $component The component to render
     * @param mixed $props The props to pass to the component
     * @return Response The decorated response
     */
    #[Override]
    public function render(string $component, $props = []): Response
    {
        $response = parent::render($component, $props);

        $decorator = new ResponseDecorator($response);
        
        if ($this->shouldLoadStaticProps()) {
            return $decorator->resolveWithStaticProps();
        } else {
            return $decorator->resolveWithoutStaticProps();
        }
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
        return app(StaticPropsReloader::class)->isReloadRequested();
    }

}