<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserCreateType;
use App\Form\UserUpdateType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\ImageProcessing;
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
    private const USERS_PER_PAGE = 10;

    public function __construct(private ImageProcessing $imageProcessing)
    {

    }

    #[Route('/', name: 'index_user')]
    public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $sort = $request->query->get('sort', 'u.id');
        $direction = $request->query->get('direction', 'asc');

        $search = trim($request->query->get('search'));

        $query = $userRepository->createSearchAndSortQueryBuilder($search, $sort, $direction);

        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), self::USERS_PER_PAGE);

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/user/index_search.html.twig', ['pagination' => $pagination]);
        }

        return $this->render('admin/user/index.html.twig', ['pagination' => $pagination]);
    }


    #[Route('/create', name: 'create_user', methods: ['POST', 'GET'])]
    public function create(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();

        $form = $this->createForm(UserCreateType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                $this->imageProcessing->save($user, $avatarFile, 'user');
            }

            $user->setPassword($passwordHasher->hashPassword($user, $request->request->all()['user_create']['password']));
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'User successfully created');

            return $this->redirectToRoute('show_user', ['id' => $user->getId()]);
        }

        return $this->render('admin/user/create.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/edit', name: 'edit_user', methods: ['POST', 'GET'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        $userThumbnail = $user ? $user->getThumbnail() : null;

        $form = $this->createForm(UserUpdateType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                $this->imageProcessing->removeAvatar($user);
                $this->imageProcessing->save($user, $avatarFile, 'user');
            }

            if ($form->get('removeFile')->getData()) {
                $this->imageProcessing->removeAvatar($user);
                $user->setAvatar(null);
                $user->setThumbnail(null);
            }

            $entityManager->flush();
            $this->addFlash('success', 'User successfully updated');

            return $this->redirectToRoute('show_user', ['id' => $user->getId()]);
        }

        return $this->render('admin/user/edit.html.twig', ['user' => $user, 'form' => $form, 'userThumbnail' => $userThumbnail]);
    }


    #[Route('/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if ($user->getPosts()) {
            foreach ($user->getPosts() as $post) {
                $this->imageProcessing->removeImage($post);
            }
        }

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

        return $this->render('admin/user/show.html.twig', ['user' => $user, 'posts' => $posts]);
    }
}
