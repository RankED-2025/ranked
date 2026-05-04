<?php

namespace App\Tests\Controller\Referentiels;

use App\Factory\EleveFactory;
use App\Factory\MatiereFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ReferentielControllerTest extends WebTestCase
{
    use ResetDatabase;

    public function testMatieresAreReturnedInAlphabeticalOrder(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'referentiel.eleve@example.com',
            'password' => 'password123',
        ]);

        MatiereFactory::createOne(['libelle' => 'Zeta']);
        MatiereFactory::createOne(['libelle' => 'Alpha']);
        MatiereFactory::createOne(['libelle' => 'Bravo']);

        $token = $this->authenticateAndGetToken($client, 'referentiel.eleve@example.com', 'password123');

        $client->request(
            'GET',
            '/api/referentiels/matieres',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertSame(['Alpha', 'Bravo', 'Zeta'], array_column($responseData, 'libelle'));
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('libelle', $responseData[0]);
    }

    private function authenticateAndGetToken($client, string $email, string $password): string
    {
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

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        return $responseData['token'];
    }
}