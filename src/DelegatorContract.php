<?php

namespace JesseGall\InertiaStaticProps;

/**
 * @template T
 */
interface DelegatorContract
{

    /**
     * @var T
     */
    public mixed $delegate {
        get;
    }

}