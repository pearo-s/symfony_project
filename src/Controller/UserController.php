<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use function PHPUnit\Framework\isEmpty;

final class UserController extends AbstractController
{
    #[Route('/users', name: 'index_user')]
    public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request)
    {
        $query = $userRepository->createQueryBuilder('u');

        $sort = $request->query->get('sort', 'u.id');
        $direction = $request->query->get('direction', 'asc');
        //dd($direction);
        $search = trim($request->query->get('search'));

        if ($search !== '') {
            $query->andWhere('u.name LIKE :q OR u.surname LIKE :q OR u.username LIKE :q')
                ->setParameter('q', '%' . $search . '%');
        }

        $query->orderBy($sort, $direction);

        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), 10);

        return $this->render('user/index.html.twig', ['pagination' => $pagination]);
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


    #[Route('/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('index_user');
    }


    #[Route('/users/{id}', name: 'show_user')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        $posts = $user->getPosts();

        return $this->render('user/show.html.twig', ['user' => $user, 'posts' => $posts]);
    }
}
