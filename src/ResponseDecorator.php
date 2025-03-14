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
        $this->delegateTo($delegate);

        $this->staticProps = $this->resolveStaticProps();

        $this->removeStaticProps();
    }

    /**
     * Add static props to the response.
     *
     * @return void
     */
    public function loadStaticProps(): void
    {
        foreach ($this->staticProps as $key => $prop) {
            if (array_key_exists($key, $this->props)) {
                continue;
            }

            $this->props[$key] = $prop->asClosure();
        }

        // Store a list of static props for the client to use.
        $this->props['staticProps'] = array_keys($this->staticProps);
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

}