<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 100; $i++) {
            $post = new Post();
            $post->setTitle($faker->sentence);
            $post->setContent($faker->paragraph);

            $manager->persist($post);
        }

        $manager->flush();
    }
}
