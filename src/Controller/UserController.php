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
    #[Route('/users', name: 'index_user')]
    public function index(EntityManagerInterface $entityManager)
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        return $this->render('user/index.html.twig', ['users' => $users]);
    }

    #[Route('/users', name: 'create_user', methods: ['POST'])]
    public function createUser(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $user->setName('John3');
        $user->setSurname('Smith');
        $user->setUsername('john_smith');
        $user->setEmail('john@gmail.com');
        $user->setPassword($passwordHasher->hashPassword($user, '123123123'));

        $entityManager->persist($user);
        $entityManager->flush();

        return new Response('New user with id ' . $user->getId());
    }

    #[Route('/users/{id}', name: 'show_user')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        return $this->render('user/show.html.twig', ['user' => $user]);
    }
}
