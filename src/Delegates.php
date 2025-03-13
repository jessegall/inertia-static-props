<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Support\Traits\ForwardsCalls;

trait Delegates
{
    use ForwardsCalls;

    protected function initializeProperties(): void
    {
        foreach (get_object_vars($this->delegate) as $key => $value) {
            $this->{$key} = &$this->delegate->{$key};
        }
    }

    public function __call($name, $arguments): mixed
    {
        return $this->forwardDecoratedCallTo($this->delegate, $name, $arguments);
    }

    public function __get($name)
    {
        return $this->delegate->{$name};
    }

    public function __set($name, $value)
    {
        $this->delegate->{$name} = $value;
    }

}