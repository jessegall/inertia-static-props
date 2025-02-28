<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Support\Traits\ForwardsCalls;
use Inertia\ResponseFactory;

/**
 * @mixin \Inertia\ResponseFactory
 */
class ResponseFactoryDecorator
{
    use ForwardsCalls;

    protected bool $loadStaticProps = false;

    public function __construct(
        protected readonly ResponseFactory $factory
    ) {}

    public function __call(string $name, array $arguments)
    {
        return $this->forwardCallTo($this->factory, $name, $arguments);
    }

    public function render(...$args)
    {
        return new ResponseDecorator(
            response: $this->factory->render(...$args),
            loadStaticProps: $this->loadStaticProps || session()->pull('inertia.reload-static-props', false)
        );
    }

    public function loadStaticProps(): static
    {
        $this->loadStaticProps = true;

        return $this;
    }


}