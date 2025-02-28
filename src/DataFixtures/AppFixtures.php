<?php

namespace App\DataFixtures;

use App\Entity\Commentary;
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
        $users = [];

        for ($i = 0; $i < 100; $i++) {
            $user = new User;
            $user->setName($faker->firstName());
            $user->setSurname($faker->lastName());
            $user->setUsername($faker->userName());
            $user->setEmail($faker->email());
            $user->setPassword($this->passwordHasher->hashPassword($user, $faker->password()));
            $user->setDob(\DateTime::createFromFormat('Y-m-d', $faker->date('Y-m-d')));
            $user->setRoles(['ROLE_USER']);

            $manager->persist($user);
            $users[] = $user;
        }


            foreach ($users as $user) {
                $postCount = rand(1, 5);
                for ($j = 0; $j < $postCount; $j++) {
                    $post = new Post();
                    $post->setUser($user);
                    $post->setTitle($faker->sentence(3));
                    $post->setText($faker->text(1000));
                    $post->setIsPublished($faker->numberBetween(0, 1));
                    $post->setCreatedAt();
                    $post->setUpdatedAt();

                    $manager->persist($post);

                    $commentaryCount = rand(5, 10);
                    for ($x = 1; $x <= $commentaryCount; $x++) {
                        $randomUser = $users[array_rand($users)];
                        $commentary = new Commentary();
                        $commentary->setPost($post);
                        $commentary->setUser($randomUser);
                        $commentary->setText($faker->paragraph);
                        $commentary->setCreatedAt();
                        $commentary->setUpdatedAt();

                        $manager->persist($commentary);
                    }
                }
            }

        $manager->flush();
    }
}
