<?php

namespace App\Tests\Traits;

/** @extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase */
trait GetsContainerServices
{
    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function getService(string $class): object
    {
        return static::getContainer()->get($class);
    }
}
