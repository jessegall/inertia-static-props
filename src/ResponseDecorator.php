<?php

namespace JesseGall\InertiaStaticProps;

use Inertia\Response;

/**
 * @implements Decorator<Response>
 */
class ResponseDecorator extends Response implements Decorator
{
    use Delegates;

    public function __construct(
        public readonly object $delegate,
    )
    {
        // Skip parent constructor as we're delegating property calls
        $this->initializePropertyDelegation();
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
                $value = $value();
                $this->props[$key] = fn() => $value;
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