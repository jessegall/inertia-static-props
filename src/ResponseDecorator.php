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

    public function __construct(
        public readonly mixed $delegate,
        public readonly bool $loadStaticProps,
    )
    {
        // We can skip the parent constructor call because we're delegating all property calls to the delegate
        $this->initializePropertyDelegation();
    }

    #[Override]
    public function resolveProperties(Request $request, array $props): array
    {
        $props = parent::resolveProperties($request, $props);

        if ($this->loadStaticProps || ! $request->header(Header::INERTIA)) {
            $props = $this->resolveStaticProps($props);
        } else {
            $props = $this->unloadStaticProps($props);
        }

        return $props;
    }

    public function resolveStaticProps(array $props): array
    {
        $staticProps = $this->getStatic();

        foreach ($staticProps as $key => $prop) {
            $props[$key] = $prop();
        }

        $props['staticProps'] = array_keys($staticProps);

        return $props;
    }

    public function unloadStaticProps(array $props): array
    {
        $staticProps = $this->getStatic();

        foreach ($staticProps as $key => $prop) {
            unset($props[$key]);
        }

        return $props;
    }

    public function getStatic(): array
    {
        return array_filter($this->props, fn($prop) => $prop instanceof StaticProp);
    }

}