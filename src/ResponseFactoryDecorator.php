<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Support\Traits\ForwardsCalls;
use Inertia\ResponseFactory;
use Inertia\Support\Header;
use ReflectionClass;
use ReflectionProperty;

/**
 * @mixin \Inertia\ResponseFactory
 */
class ResponseFactoryDecorator
{
    use ForwardsCalls;

    protected bool $loadStaticProps = false;

    protected ReflectionProperty $sharedProps;

    public function __construct(
        protected readonly ResponseFactory $factory
    )
    {
        $reflection = new ReflectionClass($factory);
        $this->sharedProps = $reflection->getProperty('sharedProps');
    }

    public function __call(string $name, array $arguments)
    {
        return $this->forwardCallTo($this->factory, $name, $arguments);
    }

    /**
     * Render the Inertia response with static props handling.
     *
     * Instead of returning a custom Response class, this method temporarily modifies
     * the shared props at the factory level. This approach ensures better compatibility
     * with third-party packages and reduces complexity.
     *
     * @param mixed ...$args Arguments to pass to the original render method
     * @return \Inertia\Response The rendered Inertia response
     */
    public function render(...$args)
    {
        $originalProps = $this->factory->getShared();

        if ($this->shouldLoadStaticProps()) {
            $props = $this->normalizeStaticProps($originalProps);
        } else {
            $props = $this->removeStaticProps($originalProps);
        }

        $this->setSharedProps($props);

        $response = $this->factory->render(...$args);

        $this->setSharedProps($originalProps);

        return $response;
    }

    public function loadStaticProps(): static
    {
        $this->loadStaticProps = true;

        return $this;
    }

    protected function getStaticProps(array $props): array
    {
        return array_filter($props, fn($prop) => $prop instanceof StaticProp);
    }

    protected function shouldLoadStaticProps(): bool
    {
        return $this->loadStaticProps ||
            session()->pull('inertia.reload-static-props', false) ||
            ! request()->header(Header::INERTIA);
    }

    protected function normalizeStaticProps(array $props): array
    {
        $staticProps = $this->getStaticProps($props);

        foreach ($staticProps as $key => $prop) {
            $props[$key] = $prop();
        }

        $props['staticProps'] = array_keys($staticProps);

        return $props;
    }

    protected function removeStaticProps(array $props): array
    {
        return array_filter($props, fn($prop) => ! ($prop instanceof StaticProp));
    }

    protected function setSharedProps(array $props): void
    {
        $this->sharedProps->setValue($this->factory, $props);
    }

}