<?php

namespace App\Tests\Controller\Auth;

use App\Entity\Eleve;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginTest extends WebTestCase
{
    private function createUser(string $email, string $plainPassword): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = new Eleve();
        $user->setEmail($email);
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        $user->setRoles(['ROLE_ELEVE']);
        $user->setName('Test');
        $user->setFirstname('Test');

        $em->persist($user);
        $em->flush();
    }

    public function testLoginSuccess(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $this->createUser($email, $password);

        $client = static::createClient();
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password,
            ])
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testLoginFailWithWrongPassword(): void
    {
        $email = 'test-fail@example.com';
        $password = 'correctPassword';
        $this->createUser($email, $password);

        $client = static::createClient();

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => 'wrongPassword',
            ])
        );

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }
}