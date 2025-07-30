<?php

namespace JesseGall\InertiaStaticProps;

use Closure;

class ResponseMacro
{

    public static function make(): Closure
    {
        return function () {
            app(Context::class)->requestStaticPropsReload();
            return $this;
        };
    }

}