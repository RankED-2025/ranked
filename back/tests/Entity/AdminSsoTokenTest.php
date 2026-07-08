<?php

namespace App\Tests\Entity;

use App\Entity\AdminSsoToken;
use App\Entity\Eleve;
use PHPUnit\Framework\TestCase;

class AdminSsoTokenTest extends TestCase
{
    private function createUser(): Eleve
    {
        $eleve = new Eleve();
        $eleve->setEmail('admin@example.com');
        $eleve->setName('Doe');
        $eleve->setFirstname('John');
        $eleve->setPassword('hashed');
        return $eleve;
    }

    public function testConstructorSetsProperties(): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('+60 seconds');
        $token = new AdminSsoToken($user, $expiresAt);

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
        $expiresAt = new \DateTimeImmutable('+60 seconds');
        $token = new AdminSsoToken($user, $expiresAt);

        $this->assertFalse($token->isExpired());
    }

    public function testIsExpiredWhenExpired(): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('-1 second');
        $token = new AdminSsoToken($user, $expiresAt);

        $this->assertTrue($token->isExpired());
    }

    public function testIsValidWhenNotUsedAndNotExpired(): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('+60 seconds');
        $token = new AdminSsoToken($user, $expiresAt);

        $this->assertTrue($token->isValid());
    }

    public function testIsValidWhenUsed(): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('+60 seconds');
        $token = new AdminSsoToken($user, $expiresAt);
        $token->markAsUsed();

        $this->assertFalse($token->isValid());
        $this->assertTrue($token->isUsed());
    }

    public function testIsValidWhenExpired(): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('-1 second');
        $token = new AdminSsoToken($user, $expiresAt);

        $this->assertFalse($token->isValid());
    }

    public function testMarkAsUsed(): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('+60 seconds');
        $token = new AdminSsoToken($user, $expiresAt);

        $token->markAsUsed();

        $this->assertTrue($token->isUsed());
    }
}
