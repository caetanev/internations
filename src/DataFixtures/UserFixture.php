<?php

namespace App\DataFixtures;

use App\Entity\Group;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {

        foreach($this->dummyUserList() as $users){

            $user = new User();
            $user->setUsername($users['username']);
            $user->setPassword($this->encoder->encodePassword($user, $users['password']));
            $user->setRole($users['role']);

            $manager->persist($user);
        }

        $manager->flush();

        // Load the relationship between User and Group

        $userRepository = $manager->getRepository(User::class);
        $groupRepository = $manager->getRepository(Group::class);

        foreach($this->dummyUserList() as $users){

            $user = $userRepository->findOneBy(['username' => $users['username']]);

            foreach($users['groups'] as $groupName){
                $group = $groupRepository->findOneBy(['name' => $groupName]);
                $user->addGroup($group);
            }

            $manager->persist($user);
        }

        $manager->flush();

    }

    private function dummyUserList(){
        return [
            [
                'username' => 'administrator',
                'password' => 'secret123',
                'role' => 'ROLE_ADMINISTRATOR',
                'groups' => ['Group V','Group W','Group Y']
            ],[
                'username' => 'user',
                'password' => 'secret456',
                'role' => 'ROLE_USER',
                'groups' => ['Group V','Group W','Group X','Group Y']
            ],[
                'username' => 'userA',
                'password' => 'secret456',
                'role' => 'ROLE_USER',
                'groups' => ['Group W','Group Y']
            ],[
                'username' => 'userB',
                'password' => 'secret456',
                'role' => 'ROLE_USER',
                'groups' => ['Group V','Group W']
            ],[
                'username' => 'userC',
                'password' => 'secret456',
                'role' => 'ROLE_USER',
                'groups' => ['Group V','Group X']
            ],[
                'username' => 'userD',
                'password' => 'secret456',
                'role' => 'ROLE_USER',
                'groups' => ['Group W','Group X','Group Y']
            ],[
                'username' => 'userE',
                'password' => 'secret456',
                'role' => 'ROLE_USER',
                'groups' => ['Group V','Group W','Group X']
            ],[
                'username' => 'userF',
                'password' => 'secret456',
                'role' => 'ROLE_USER',
                'groups' => ['Group V','Group X','Group Y']
            ],[
                'username' => 'userG',
                'password' => 'secret456',
                'role' => 'ROLE_USER',
                'groups' => ['Group V','Group W','Group X','Group Y']
            ],
        ];
    }

}
