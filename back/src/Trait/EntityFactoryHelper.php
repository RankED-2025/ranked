<?php

namespace App\Trait;

use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * Provide helper methods for entities factories.
 */
trait EntityFactoryHelper
{
    /**
     * Alias for this code piece:
     * ```
     * LazyValue::new(function() {
     *      $existing = $factory::repository()->findAll();
     *
     *      return count($existing) > 0
     *          ? self::faker()->randomElement($existing)
     *          : $factory::new();
     * });
     * ```
     * @param class-string<PersistentObjectFactory<T_TargetEntity>> $factoryClass
     * @template T_TargetEntity
     * @return LazyValue
     */
    protected static function fromLazyFactoryValue(string $factoryClass): LazyValue
    {
        return LazyValue::new(function() use($factoryClass) {
            $existing = $factoryClass::repository()->findAll();

            return count($existing) > 0
                ? self::faker()->randomElement($existing)
                : $factoryClass::new();
        });
    }
}
