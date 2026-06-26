<?php

namespace App\Tests\Entity;

use App\Entity\Eleve;
use App\Entity\PasswordResetToken;
use PHPUnit\Framework\TestCase;

class PasswordResetTokenTest extends TestCase
{
    private function createUser(): Eleve
    {
        $eleve = new Eleve();
        $eleve->setEmail('test@example.com');
        $eleve->setName('Doe');
        $eleve->setFirstname('John');
        $eleve->setPassword('hashed');
        return $eleve;
    }

    public function testConstructorSetsProperties(): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $token = new PasswordResetToken($user, $expiresAt);

        $this->assertNull($token->getId());
        $this->assertSame($user, $token->getUser());
        $this->assertSame($expiresAt, $token->getExpiresAt());
        $this->assertFalse($token->isUsed());
        $this->assertNotEmpty($token->getToken());
        $this->assertSame(64, strlen($token->getToken()));
    }

    public function testIsExpiredWhenNotExpired(): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $token = new PasswordResetToken($user, $expiresAt);

        $this->assertFalse($token->isExpired());
    }

    public function testIsExpiredWhenExpired(): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('-1 hour');
        $token = new PasswordResetToken($user, $expiresAt);

        $this->assertTrue($token->isExpired());
    }

    public function testIsValidWhenNotUsedAndNotExpired(): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $token = new PasswordResetToken($user, $expiresAt);

        $this->assertTrue($token->isValid());
    }

    public function testIsValidWhenUsed(): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $token = new PasswordResetToken($user, $expiresAt);
        $token->markAsUsed();

        $this->assertFalse($token->isValid());
        $this->assertTrue($token->isUsed());
    }

    public function testIsValidWhenExpired(): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('-1 hour');
        $token = new PasswordResetToken($user, $expiresAt);

        $this->assertFalse($token->isValid());
    }

    public function testMarkAsUsed(): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $token = new PasswordResetToken($user, $expiresAt);

        $token->markAsUsed();

        $this->assertTrue($token->isUsed());
    }

    public function testUserGetCreatedAt(): void
    {
        $user = $this->createUser();
        $date = new \DateTimeImmutable('2024-01-01');
        $user->setCreatedAt($date);

        $this->assertSame($date, $user->getCreatedAt());
    }
}
