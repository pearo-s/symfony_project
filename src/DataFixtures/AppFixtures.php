<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 20; $i++) {
            $user = new User;
            $user->setName($faker->firstName());
            $user->setSurname($faker->lastName());
            $user->setUsername($faker->userName());
            $user->setEmail($faker->email());
            $user->setPassword($this->passwordHasher->hashPassword($user, $faker->password()));
            $user->setDob(\DateTime::createFromFormat('Y-m-d', $faker->date('Y-m-d')));

            $manager->persist($user);

            $postCount = rand(1, 5);
            for ($j = 1; $j <= $postCount; $j++) {
                $post = new Post();
                $post->setUser($user);
                $post->setTitle($faker->sentence(3));
                $post->setText($faker->paragraph);
                $post->setIsPublished($faker->numberBetween(0, 1));
                $post->setCreatedAt();
                $post->setUpdatedAt();

                $manager->persist($post);
            }
        }

        $manager->flush();
    }
}
