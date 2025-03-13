<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @phpstan-require-implements DelegatorContract
 */
trait Delegates
{
    use ForwardsCalls;

    /**
     * Initialize bidirectional property delegation between this class and its delegate.
     *
     * This method creates reference links between all properties of the delegate object
     * and the corresponding properties in the current class. After initialization,
     * changes to properties in either object will be reflected in both objects.
     *
     * @return void
     */
    protected function initializePropertyDelegation(): void
    {
        $self = $this;

        $linker = function () use ($self) {
            $properties = array_keys(get_object_vars($this));

            foreach ($properties as $property) {
                $self->{$property} = &$this->{$property};
            }
        };

        $linker->call($this->delegate);
    }

    /**
     * Forward a method call to the given object, returning $this if the forwarded call returned itself.
     *
     * The delegate might have methods that are not defined on the decorator.
     * This method allows us to call those methods on the delegate.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments): mixed
    {
        return $this->forwardDecoratedCallTo($this->delegate, $name, $arguments);
    }

}