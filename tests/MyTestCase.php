<?php


namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MyTestCase extends WebTestCase
{

    /**
     * @var Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Create a client with a default Authorization header.
     *
     * @param string $username
     * @param string $password
     *
     * @return Symfony\Bundle\FrameworkBundle\Client
     */
    protected function createAuthenticatedClient($username = 'user', $password = 'password')
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/auth/check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $username,
                'password' => $password,
            ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }

    /**
     * Create an instance of the entityManager in the class
     */
    protected function setUpEntityManager()
    {
        if(null === $this->entityManager){
            $kernel = self::bootKernel();
            $this->entityManager = $kernel->getContainer()
                ->get('doctrine')
                ->getManager();
        }
    }

    /**
     * Authenticate the client as Administrator user
     */
    protected function givenIAmAuthenticatedAsAdministrator()
    {
        $this->client = $this->createAuthenticatedClient('administrator', 'secret123');
    }

    /**
     * Authenticate the client as NON Administrator user
     */
    protected function givenIAmNotAuthenticatedAsAdministrator()
    {
        $this->client = $this->createAuthenticatedClient('user', 'secret456');
    }

    protected function thenIShouldGet201Response(){

        $this->assertSame(
            201, $this->client->getResponse()->getStatusCode()
        );
    }

    protected function thenIShouldGet203Response(){

        $this->assertSame(
            203, $this->client->getResponse()->getStatusCode()
        );
    }

    protected function thenIShouldGet204Response(){

        $this->assertSame(
            204, $this->client->getResponse()->getStatusCode()
        );
    }

    protected function thenIShouldGet400Response(){
        $this->assertSame(
            400, $this->client->getResponse()->getStatusCode()
        );
    }

    protected function thenIShouldGet403Response(){

        $this->assertSame(
            403, $this->client->getResponse()->getStatusCode()
        );
    }


}