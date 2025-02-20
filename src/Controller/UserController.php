<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function createUser(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $user->setName('John');
        $user->setSurname('Smith');
        $user->setUsername('john_smith');
        $user->setEmail('john@gmail.com');
        $user->setPassword($passwordHasher->hashPassword($user, '123123123'));

        $entityManager->persist($user);
        $entityManager->flush();

        return new Response('New user with id ' . $user->getId());
    }
}
