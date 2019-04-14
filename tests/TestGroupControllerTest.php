<?php

namespace App\Tests;

use App\Entity\User;
use App\Entity\Group;

class TestGroupControllerTest extends MyTestCase
{

    public $feature = 'Tests the Groups interactions in the API';

    protected function whenICreateAGroupWith_Properties($properties)
    {
        $this->client->request('POST', '/api/groups', $properties);
    }

    protected function whenIDeleteAGroupWithoutUsers($groupName)
    {
        $this->setUpEntityManager();

        // Get the Group from database that was loaded without users in the Fixture
        $groupRepository = $this->entityManager->getRepository(Group::class);
        $group = $groupRepository->findOneBy(['name' => $groupName]);

        // Then proceed with the operation
        $url = "/api/groups/{$group->getId()}";
        $this->client->request('DELETE', $url);
    }

    protected function whenIDeleteAGroupWithUsers()
    {
        $this->setUpEntityManager();

        // Get the Group V id from database, this group is loaded with users in the Fixture
        $groupRepository = $this->entityManager->getRepository(Group::class);
        $group = $groupRepository->findOneBy(['name' => 'Group V']);

        // Then proceed with the operation
        $url = "/api/groups/{$group->getId()}";
        $this->client->request('DELETE', $url);
    }

    protected function whenIAssignANewUserToAGroup()
    {
        $this->setUpEntityManager();

        /**
         * Get the Group V id from database, this group doesn't have UserA
         */
        $groupRepository = $this->entityManager->getRepository(Group::class);
        $group = $groupRepository->findOneBy(['name' => 'Group V']);

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => 'userA']);

        // Then proceed with the operation
        $url = "/api/groups/{$group->getId()}/users";
        $this->client->request('POST', $url, ['userId' => $user->getId()]);
    }

    protected function whenIAssignAExistingUserToAGroup()
    {
        $this->setUpEntityManager();

        /**
         * Get the Group W id from database, this group have UserB
         */
        $groupRepository = $this->entityManager->getRepository(Group::class);
        $group = $groupRepository->findOneBy(['name' => 'Group W']);

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => 'userB']);

        $url = "/api/groups/{$group->getId()}/users";
        $this->client->request('POST', $url, ['userId' => $user->getId()]);
    }

    protected function whenIRemoveAUserFromAGroup()
    {
        $this->setUpEntityManager();

        /**
         * Get the Group X, this group have UserD
         */
        $groupRepository = $this->entityManager->getRepository(Group::class);
        $group = $groupRepository->findOneBy(['name' => 'Group X']);

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => 'userD']);

        $url = "/api/groups/{$group->getId()}/users/{$user->getId()}";
        $this->client->request('DELETE', $url);
    }

    public function testScenarioCreateValidGroupAsAdministrator(){

        $validGroupData = [
            'name' => 'testGroup'
        ];

        $this->givenIAmAuthenticatedAsAdministrator();
        $this->whenICreateAGroupWith_Properties($validGroupData);
        $this->thenIShouldGet201Response();

    }

    public function testScenarioCreateInvalidGroupAsAdministrator(){

        $invalidGroupData = [
            'name' => ''
        ];

        $this->givenIAmAuthenticatedAsAdministrator();
        $this->whenICreateAGroupWith_Properties($invalidGroupData);
        $this->thenIShouldGet400Response();

    }

    public function testScenarioCreateValidGroupAsNonAdministrator(){

        $validUserData = [
            'name' => 'testGroupAsUser'
        ];

        $this->givenIAmNotAuthenticatedAsAdministrator();
        $this->whenICreateAGroupWith_Properties($validUserData);
        $this->thenIShouldGet403Response();

    }

    public function testScenarioDeleteGroupWithoutUsersAsAdministrator(){

        $this->givenIAmAuthenticatedAsAdministrator();
        $this->whenIDeleteAGroupWithoutUsers('Group S');
        $this->thenIShouldGet204Response();
    }

    public function testScenarioDeleteGroupWithoutUsersAsNonAdministrator(){

        $this->givenIAmNotAuthenticatedAsAdministrator();
        $this->whenIDeleteAGroupWithoutUsers('Group R');
        $this->thenIShouldGet403Response();
    }

    public function testScenarioDeleteGroupWithUsersAsAdministrator(){

        $this->givenIAmAuthenticatedAsAdministrator();
        $this->whenIDeleteAGroupWithUsers();
        $this->thenIShouldGet203Response();
    }

    public function testScenarioAssignNewUserToAGroupAsAdministrator()
    {
        $this->givenIAmAuthenticatedAsAdministrator();
        $this->whenIAssignANewUserToAGroup();
        $this->thenIShouldGet201Response();
    }

    public function testScenarioAssignNewUserToAGroupAsNonAdministrator()
    {
        $this->givenIAmNotAuthenticatedAsAdministrator();
        $this->whenIAssignANewUserToAGroup();
        $this->thenIShouldGet403Response();
    }

    public function testScenarioAssignExistingUserToAGroupAsAdministrator()
    {
        $this->givenIAmAuthenticatedAsAdministrator();
        $this->whenIAssignAExistingUserToAGroup();
        $this->thenIShouldGet203Response();

    }

    public function testScenarioRemoveUserFromAGroupAsAdministrator()
    {
        $this->givenIAmAuthenticatedAsAdministrator();
        $this->whenIRemoveAUserFromAGroup();
        $this->thenIShouldGet204Response();
    }

    public function testScenarioRemoveUserFromAGroupAsNonAdministrator()
    {
        $this->givenIAmNotAuthenticatedAsAdministrator();
        $this->whenIRemoveAUserFromAGroup();
        $this->thenIShouldGet403Response();
    }

}
