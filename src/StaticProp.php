<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Support\Facades\App;

class StaticProp
{

    /** @var callable */
    protected $value;

    public function __construct(callable $value)
    {
        $this->value = $value;
    }

    public function __invoke()
    {
        return App::call($this->value);
    }

}