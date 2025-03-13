<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\Support\Header;
use Override;

/**
 * @implements DelegatorContract<Response>
 */
class ResponseDecorator extends Response implements DelegatorContract
{
    use Delegates;

    /**
     * @param mixed $delegate The object being decorated
     * @param bool $loadStaticProps Whether to load static properties
     */
    public function __construct(
        public readonly mixed $delegate,
        public readonly bool $loadStaticProps,
    )
    {
        // Skip parent constructor as we're delegating property calls
        $this->initializePropertyDelegation();
    }

    /**
     * Resolve properties with static props handling
     *
     * @param Request $request The current request
     * @param array $props Properties to resolve
     * @return array Resolved properties
     */
    #[Override]
    public function resolveProperties(Request $request, array $props): array
    {
        $props = parent::resolveProperties($request, $props);

        return $this->loadStaticProps
            ? $this->resolveStaticProps($props)
            : $this->unloadStaticProps($props);
    }

    /**
     * Resolve and add static properties to props array
     *
     * @param array $props
     * @return array
     */
    public function resolveStaticProps(array $props): array
    {
        $staticProps = $this->getStaticProps();

        foreach ($staticProps as $key => $prop) {
            $props[$key] = $prop();
        }

        $props['staticProps'] = array_keys($staticProps);

        return $props;
    }

    /**
     * Remove static properties from props array
     *
     * @param array $props
     * @return array
     */
    public function unloadStaticProps(array $props): array
    {
        $staticProps = $this->getStaticProps();

        foreach (array_keys($staticProps) as $key) {
            unset($props[$key]);
        }

        return $props;
    }

    /**
     * Get all static properties
     *
     * @return array<string, StaticProp>
     */
    public function getStaticProps(): array
    {
        return array_filter($this->props, fn($prop) => $prop instanceof StaticProp);
    }

}