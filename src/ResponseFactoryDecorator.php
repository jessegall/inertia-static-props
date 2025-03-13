<?php

namespace JesseGall\InertiaStaticProps;

use Inertia\Response;
use Inertia\ResponseFactory;
use Inertia\Support\Header;
use Override;

/**
 * @implements DelegatorContract<ResponseFactory>
 */
class ResponseFactoryDecorator extends ResponseFactory implements DelegatorContract
{
    use Delegates;

    protected static bool $loadStaticProps = false;

    public function __construct(
        public readonly mixed $delegate
    )
    {
        $this->delegateProperties();
    }

    #[Override]
    public function render(string $component, $props = []): Response
    {
        $delegate = parent::render($component, $props);

        return new ResponseDecorator($delegate, $this->shouldLoadStaticProps());
    }

    protected function shouldLoadStaticProps(): bool
    {
        return self::$loadStaticProps
            || session()->pull('inertia.reload-static-props', false)
            || ! request()->header(Header::INERTIA);
    }

    public static function loadStaticProps(): void
    {
        static::$loadStaticProps = true;
    }

}