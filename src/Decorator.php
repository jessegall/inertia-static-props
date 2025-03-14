<?php

namespace JesseGall\InertiaStaticProps;

/**
 * @template T of object
 */
interface Decorator
{

    /**
     * @param T $delegate
     * @return void
     */
    public function decorate(object $delegate): void;

}