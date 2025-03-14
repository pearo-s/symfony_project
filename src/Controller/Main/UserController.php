<?php

namespace App\Controller\Main;

use App\Entity\User;
use App\Form\UserCreateType;
use App\Form\UserUpdateType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/users')]
final class UserController extends AbstractController
{
    #[Route('/{id}/edit', name: 'main_edit_user', methods: ['POST', 'GET'])]
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

        return $this->render('admin/user/edit.html.twig', ['user' => $user, 'form' => $form]);
    }


    #[Route('/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, PostRepository $postRepository, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if ($user->getPosts()) {
            foreach ($user->getPosts() as $post) {
                $postRepository->removeImageFiles($post);
            }
        }

        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'User successfully deleted');

        return $this->redirectToRoute('index_user');
    }


    #[Route('/{id}', name: 'main_show_user')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if ($user) {
            $posts = $user->getPosts();
        } else {
            $posts = null;
        }

        return $this->render('main/user/show.html.twig', ['user' => $user, 'posts' => $posts]);
    }
}
