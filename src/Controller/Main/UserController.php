<?php

namespace App\Controller\Main;

use App\Entity\User;

use App\Form\UserUpdateType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


#[Route('/users')]
final class UserController extends AbstractController
{
    #[Route('/{id}/edit', name: 'main_edit_user', methods: ['POST', 'GET'])]
    public function edit(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        $userThumbnail = $user ? $user->getThumbnail() : null;

        $form = $this->createForm(UserUpdateType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                $userRepository->removeAvatarFiles($user);
                $userRepository->saveAvatar($user, $avatarFile);
            }

            if ($form->get('removeFile')->getData()) {
                $userRepository->removeAvatarFiles($user);
                $user->setAvatar(null);
                $user->setThumbnail(null);
            }

            $entityManager->flush();
            $this->addFlash('success', 'User successfully updated');

            return $this->redirectToRoute('main_show_user', ['id' => $user->getId()]);
        }

        return $this->render('main/user/edit.html.twig', ['user' => $user, 'form' => $form, 'userThumbnail' => $userThumbnail]);
    }


    #[Route('/{id}', name: 'main_delete_user', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, UserRepository $userRepository, PostRepository $postRepository, Request $request, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if ($this->getUser() !== $user) {
            throw new AccessDeniedException('You can only delete your own account.');
        }

        if ($user) {

            foreach ($user->getPosts() as $post) {
                $postRepository->removeImageFiles($post);
            }

            $userRepository->removeAvatarFiles($user);

            $entityManager->remove($user);
            $entityManager->flush();

            $request->getSession()->invalidate(); // clearing session and token before redirect
            $this->container->get('security.token_storage')->setToken(null);

            $this->addFlash('success', 'Your profile has been successfully deleted');

            return $this->redirectToRoute('home');
        }

        $this->addFlash('danger', 'Your profile could not be deleted');

        return $this->redirectToRoute('main_index_post');
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
