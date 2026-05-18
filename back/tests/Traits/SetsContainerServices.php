<?php

namespace App\Tests\Traits;

/** @extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase */
trait SetsContainerServices
{
    /**
     * Sets a service into the DI container.
     *
     * @template T of object
     * @param class-string<T> $class
     * @param T $service
     */
    private function setService(string $class, object $service): void
    {
        static::getContainer()->set($class, $service);
    }
}
