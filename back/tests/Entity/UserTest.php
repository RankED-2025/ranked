<?php

namespace App\Tests\Entity;

use App\Entity\Eleve;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ConcreteUser extends User
{
    public function __construct()
    {
        parent::__construct();
    }
}

class UserTest extends TestCase
{
    public function testConstructorSetsCreatedAt(): void
    {
        $user = new ConcreteUser();

        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }

    public function testGettersAndSetters(): void
    {
        $user = new ConcreteUser();
        $user->setName('Dupont');
        $user->setFirstname('Alice');
        $user->setEmail('alice@example.com');
        $user->setPassword('hashed');
        $user->setRoles(['ROLE_USER']);

        $this->assertSame('Dupont', $user->getName());
        $this->assertSame('Alice', $user->getFirstname());
        $this->assertSame('alice@example.com', $user->getEmail());
        $this->assertSame('hashed', $user->getPassword());
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertSame('alice@example.com', $user->getUserIdentifier());
    }

    public function testEraseCredentials(): void
    {
        $user = new ConcreteUser();
        $user->eraseCredentials();

        $this->assertTrue(true);
    }
}
