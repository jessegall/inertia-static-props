<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Support\Traits\ForwardsCalls;
use ReflectionClass;

trait Delegates
{
    use ForwardsCalls;

    protected function delegateProperties(): void
    {
        $reflector = new ReflectionClass($this->delegate);

        foreach ($reflector->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $this->linkReference($property->getName());
        }
    }

    private function linkReference(string $propertyName): void
    {
        $self = $this;

        $writer = function () use ($propertyName, $self) {
            $self->{$propertyName} = &$this->{$propertyName};
        };

        $writer->call($this->delegate);
    }

    public function __call($name, $arguments): mixed
    {
        return $this->forwardDecoratedCallTo($this->delegate, $name, $arguments);
    }

    public function __get($name): mixed
    {
        return $this->delegate->{$name};
    }

    public function __set($name, $value): void
    {
        $this->delegate->{$name} = $value;
    }

}