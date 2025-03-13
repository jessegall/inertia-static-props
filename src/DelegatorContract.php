<?php

namespace JesseGall\InertiaStaticProps;

/**
 * @template T
 */
interface DelegatorContract
{

    /**
     * The delegate object that will be proxied.
     *
     * @var T
     */
    public mixed $delegate {
        get;
    }

}