<?php

namespace App\Tests;

use App\Entity\User;

class TestUserControllerTest extends MyTestCase
{

    public $feature = 'Tests the User interactions in the API';

    protected function whenICreateAnUserWith_Properties($properties)
    {
        $this->client->request('POST', '/api/users', $properties);
    }

    protected function whenIDeleteAnUser($username)
    {
        $this->setUpEntityManager();

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => $username]);

        $url = "/api/users/{$user->getId()}";
        $this->client->request('DELETE', $url);
    }

    public function testScenarioCreateValidUserAsAdministrator(){

        $validUserData = [
            'username' => 'test',
            'password' => '123456',
            'role' => 'ROLE_USER',
        ];

        $this->givenIAmAuthenticatedAsAdministrator();
        $this->whenICreateAnUserWith_Properties($validUserData);
        $this->thenIShouldGet201Response();

    }

    public function testScenarioCreateInvalidUserAsAdministrator(){

        $invalidUserData = [
            'password' => '123456',
            'role' => 'ROLE_USER',
        ];

        $this->givenIAmAuthenticatedAsAdministrator();
        $this->whenICreateAnUserWith_Properties($invalidUserData);
        $this->thenIShouldGet400Response();

    }

    public function testScenarioCreateValidUserAsNonAdministrator(){

        $validUserData = [
            'username' => 'test',
            'password' => '123456',
            'role' => 'ROLE_USER',
        ];

        $this->givenIAmNotAuthenticatedAsAdministrator();
        $this->whenICreateAnUserWith_Properties($validUserData);
        $this->thenIShouldGet403Response();

    }

    public function testScenarioDeleteUserAsAdministrator(){
        $this->givenIAmAuthenticatedAsAdministrator();
        $this->whenIDeleteAnUser('userE');
        $this->thenIShouldGet204Response();
    }

    public function testScenarioDeleteUserAsNonAdministrator(){
        $this->givenIAmNotAuthenticatedAsAdministrator();
        $this->whenIDeleteAnUser('userF');
        $this->thenIShouldGet403Response();
    }

}
