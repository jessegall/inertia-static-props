<?php

namespace Tests\Feature;

use JesseGall\InertiaStaticProps\Delegates;
use JesseGall\InertiaStaticProps\DelegatorContract;
use Orchestra\Testbench\TestCase;

class DelegatorTest extends TestCase
{

    public function test_DelegatorCorrectlyProxiesPropertyAccessAndMethods()
    {
        $delegator = new DelegatorTestDelegator(
            new DelegatorTestDelegate()
        );

        $delegator->count = 1;
        $this->assertEquals(1, $delegator->getCount());
        $this->assertEquals(1, $delegator->delegate->getCount());

        $delegator->incrementCount();
        $this->assertEquals(2, $delegator->getCount());
        $this->assertEquals(2, $delegator->delegate->getCount());

        $delegator->setCount(5);
        $this->assertEquals(5, $delegator->getCount());
        $this->assertEquals(5, $delegator->delegate->getCount());
    }

    public function test_DelegatorSupportsArrayAccessUsingIndex()
    {
        $delegator = new DelegatorTestDelegator(
            new DelegatorTestDelegate()
        );

        $delegator->array['key'] = 'value';

        $this->assertEquals('value', $delegator->delegate->array['key']);
    }

    public function test_DelegatorHandlesNestedArrayProperties()
    {
        $delegator = new DelegatorTestDelegator(
            new DelegatorTestDelegate()
        );

        $delegator->array['nested']['key'] = 'nested-value';

        $this->assertEquals('nested-value', $delegator->delegate->array['nested']['key']);
    }

    public function test_DelegatorSynchronizesArrayOperations()
    {
        $delegator = new DelegatorTestDelegator(
            new DelegatorTestDelegate()
        );

        // Set initial array values
        $delegator->array = ['a' => 1, 'b' => 2];

        // Modify through delegator
        $delegator->array['c'] = 3;
        unset($delegator->array['a']);

        // Check delegate has the same modifications
        $this->assertEquals(['b' => 2, 'c' => 3], $delegator->delegate->array);
        $this->assertFalse(isset($delegator->delegate->array['a']));
    }

    public function test_DelegatorInitializesPropertiesCorrectly()
    {
        $delegate = new DelegatorTestDelegate();
        $delegate->count = 10;
        $delegate->array = ['initial' => 'value'];

        $delegator = new DelegatorTestDelegator($delegate);

        // Check that properties were initialized from the delegate
        $this->assertEquals(10, $delegator->count);
        $this->assertEquals(['initial' => 'value'], $delegator->array);
    }

    public function test_DelegatorCanAccessAndModifyPrivateProperties()
    {
        $delegator = new DelegatorTestDelegator(
            new DelegatorTestDelegate()
        );

        $delegator->setPrivateProperty('private-value');

        $this->assertEquals('private-value', $delegator->getPrivateProperty());
        $this->assertEquals('private-value', $delegator->delegate->getPrivateProperty());
    }

    public function test_DelegatorCanOverrideDelegateMethods()
    {
        $delegator = new DelegatorTestDelegator(
            new DelegatorTestDelegate()
        );

        // The setCount method is overridden in the delegator
        $delegator->setCount(5);

        // Both should reflect the change
        $this->assertEquals(5, $delegator->getCount());
        $this->assertEquals(5, $delegator->delegate->getCount());

        // Now call a method that's only in the delegator
        $delegator->multiplyCount(2);

        // This should affect both instances
        $this->assertEquals(10, $delegator->getCount());
        $this->assertEquals(10, $delegator->delegate->getCount());
    }
}

class DelegatorTestDelegate
{
    public int $count = 0;
    public array $array = [];

    private string $privateProperty = '';

    public function incrementCount(): void
    {
        $this->count++;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setPrivateProperty(string $value): void
    {
        $this->privateProperty = $value;
    }

    public function getPrivateProperty(): string
    {
        return $this->privateProperty;
    }

}

class DelegatorTestDelegator extends DelegatorTestDelegate implements DelegatorContract
{
    use Delegates;

    public function __construct(
        public readonly mixed $delegate
    )
    {
        $this->initializePropertyDelegation();
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function multiplyCount(int $multiplier): void
    {
        $this->count *= $multiplier;
    }

}