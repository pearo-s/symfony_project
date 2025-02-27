<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserCreateType;
use App\Form\UserUpdateType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
final class UserController extends AbstractController
{
    #[Route('/', name: 'index_user')]
    public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $userRepository->createQueryBuilder('u');

        $sort = $request->query->get('sort', 'u.id');
        $direction = $request->query->get('direction', 'asc');

        $search = trim($request->query->get('search'));

        if ($search !== '') {
            $query->andWhere('u.name LIKE :q OR u.surname LIKE :q OR u.username LIKE :q')
                ->setParameter('q', '%' . $search . '%');
        }

        $query->orderBy($sort, $direction);

        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), 10);

        return $this->render('user/index.html.twig', ['pagination' => $pagination]);
    }


    #[Route('/create', name: 'create_user', methods: ['POST', 'GET'])]
    public function create(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();

        $form = $this->createForm(UserCreateType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $request->request->all()['user_create']['password']));
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'User successfully created');

            return $this->redirectToRoute('show_user', ['id' => $user->getId()]);
        }

        return $this->render('user/create.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/edit', name: 'edit_user', methods: ['POST', 'GET'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        $form = $this->createForm(UserUpdateType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'User successfully updated');

            return $this->redirectToRoute('show_user', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', ['user' => $user, 'form' => $form]);
    }


    #[Route('/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'User successfully deleted');

        return $this->redirectToRoute('index_user');
    }


    #[Route('/{id}', name: 'show_user')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if ($user) {
            $posts = $user->getPosts();
        } else {
            $posts = null;
        }

        return $this->render('user/show.html.twig', ['user' => $user, 'posts' => $posts]);
    }
}
