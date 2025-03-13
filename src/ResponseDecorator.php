<?php

namespace JesseGall\InertiaStaticProps;

use Inertia\Response;

/**
 * @implements Decorator<Response>
 */
class ResponseDecorator extends Response implements Decorator
{
    /**
     * @use Delegates<Response>
     */
    use Delegates;

    public function __construct(Response $delegate)
    {
        // Skip the parent constructor as we delegate all properties
        $this->delegateTo($delegate);
    }

    public function resolveWithStaticProps(): Response
    {
        $this->loadStaticPropValues();

        return $this->delegate;
    }

    public function resolveWithoutStaticProps(): Response
    {
        $this->removeStaticProps();

        return $this->delegate;
    }

    protected function loadStaticPropValues(): void
    {
        $staticProps = [];

        foreach ($this->props as $key => $value) {
            if ($value instanceof StaticProp) {
                $this->props[$key] = $value->asClosure();
                $staticProps[] = $key;
            }
        }

        $this->props['staticProps'] = $staticProps;
    }

    protected function removeStaticProps(): void
    {
        $this->props = array_filter($this->props, fn($prop) => ! $prop instanceof StaticProp);
    }

}