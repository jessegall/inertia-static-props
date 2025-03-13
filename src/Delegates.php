<?php

namespace JesseGall\InertiaStaticProps;

use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @template T of object
 * @phpstan-require-implements Decorator<T>
 */
trait Delegates
{
    use ForwardsCalls;

    /**
     * @var T The object to delegate to
     */
    private(set) object $delegate;

    /**
     * Initialize bidirectional property delegation between this class and its delegate.
     *
     * This method creates reference links between all properties of the delegate object
     * and the corresponding properties in the current class. After initialization,
     * changes to properties in either object will be reflected in both objects.
     *
     * @param T $delegate
     * @return void
     */
    public function delegateTo(object $delegate): void
    {
        $this->delegate = $delegate;

        $self = $this;

        $linker = function () use ($self) {
            $properties = array_keys(get_object_vars($this));

            foreach ($properties as $property) {
                // Overwrite the decorator property with a reference of the delegate property.
                // This creates a direct reference so both properties share the same value.
                $self->{$property} = &$this->{$property};
            }
        };

        // Call the linker with the delegate as context.
        // This allows the linker to access all (including private) properties of the delegate.
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