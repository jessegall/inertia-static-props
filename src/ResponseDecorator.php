<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\Support\Header;

class ResponseDecorator extends Response
{

    public function __construct(
        protected readonly Response $response,
        protected readonly bool $loadStaticProps,
    )
    {
        parent::__construct(
            $response->component,
            $response->props,
            $response->rootView,
            $response->version,
            $response->encryptHistory,
        );
    }

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