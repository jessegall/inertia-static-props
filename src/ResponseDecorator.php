<?php

namespace JesseGall\InertiaStaticProps;

use Inertia\Response;

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
        // Skip the parent constructor as we delegate all properties
        $this->decorate($delegate);

        $this->staticProps = $this->resolveStaticProps();
    }

    /**
     * Remember the static properties on the response.
     *
     * @return StaticProp[]
     */
    protected function resolveStaticProps(): array
    {
        return array_filter($this->props, fn($prop) => $prop instanceof StaticProp);
    }

    /**
     * Resolve the response with static props.
     *
     * @return Response The response
     */
    public function resolveWithStaticProps(): Response
    {
        $this->loadStaticProps();

        return $this->delegate;
    }

    /**
     * Resolve the response without static props.
     *
     * @return Response
     */
    public function resolveWithoutStaticProps(): Response
    {
        $this->removeStaticProps();

        return $this->delegate;
    }

    /**
     * Prepare static prop values for the response.
     *
     * Replaces any StaticProp instances with a closure that returns
     * the value, allowing Inertia to process them normally.
     *
     * @return void
     */
    protected function loadStaticProps(): void
    {
        foreach ($this->staticProps as $key => $prop) {
            $this->props[$key] = $prop->asClosure();
        }

        // Store a list of static props for the client to use.
        $this->props['staticProps'] = array_keys($this->staticProps);
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

}