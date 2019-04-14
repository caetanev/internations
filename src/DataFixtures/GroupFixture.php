<?php

namespace App\DataFixtures;

use App\Entity\Group;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class GroupFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        foreach($this->dummyGroupList() as $item){
            $group = new Group();
            $group->setName($item['name']);
            $manager->persist($group);
        }
        $manager->flush();
    }

    private function dummyGroupList(){
        return [
            ['name' => 'Group R'],
            ['name' => 'Group S'],
            ['name' => 'Group T'],
            ['name' => 'Group U'],
            ['name' => 'Group V'],
            ['name' => 'Group W'],
            ['name' => 'Group X'],
            ['name' => 'Group Y'],
            ['name' => 'Group Z']
        ];
    }
}
