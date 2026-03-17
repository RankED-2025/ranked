<?php

namespace App\Factory;

use App\Entity\Question;
use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Question>
 */
final class QuestionFactory extends PersistentProxyObjectFactory
{
    private bool $withReponses = false;

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return Question::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'enonce' => self::faker()->realTextBetween(50, 200) . ' ?',
            'qcm'    => LazyValue::new(function () {
                $existing = QcmFactory::repository()->findAll();

                return count($existing) > 0
                    ? self::faker()->randomElement($existing)
                    : QcmFactory::new();
            }),
        ];
    }

    public function withReponses(): static
    {
        $clone = clone $this;
        $clone->withReponses = true;
        return $clone;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Question $question): void {
                if ($this->withReponses) {
                    $count = self::faker()->numberBetween(2, 5);

                    // One correct answer
                    ReponseFactory::createOne([
                        'question'  => $question,
                        'isCorrect' => true,
                    ]);

                    // Rest are wrong
                    ReponseFactory::createMany($count - 1, [
                        'question'  => $question,
                        'isCorrect' => false,
                    ]);
                }
            });
    }
}
