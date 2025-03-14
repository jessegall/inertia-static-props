<?php

namespace JesseGall\InertiaStaticProps;

class PropertyLinker
{

    /**
     * @param string $property
     * @param object $source
     * @param object $target
     * @return void
     */
    public function linkProperty(string $property, object $source, object $target): void
    {
        $linker = function () use ($property, $target) {
            // Overwrite the target property with a reference of the source property.
            // This creates a direct reference so both properties share the same value.
            $target->{$property} = &$this->{$property};
        };

        $linker->call($source);
    }

    /**
     * @param object $source
     * @param object $target
     * @param string[] $except
     * @return void
     */
    public function linkProperties(object $source, object $target, array $except = []): void
    {
        $properties = $this->resolveProperties($source, $except);

        foreach ($properties as $property) {
            $this->linkProperty($property, $source, $target);
        }
    }

    /**
     * @param string $property
     * @param object $target
     * @param mixed $value
     * @return void
     */
    public function write(string $property, object $target, mixed $value): void
    {
        $writer = function () use ($property, $value) {
            $this->{$property} = $value;
        };

        $writer->call($target);
    }

    /**
     * @param object $source
     * @param array $except
     * @return string[]
     */
    public function resolveProperties(object $source, array $except = []): array
    {
        $resolver = function () use ($source) {
            return array_keys(get_object_vars($source));
        };

        $properties = $resolver->call($source);

        return array_values(array_diff($properties, $except));
    }

}