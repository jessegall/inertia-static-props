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
    public function delegateTo(object $delegate): void;

}