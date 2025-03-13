<?php

namespace JesseGall\InertiaStaticProps;

/**
 * @template T of object
 */
interface Decorator
{

    /**
     * The delegate object that will be proxied.
     *
     * @var T
     */
    public object $delegate {
        get;
    }

}