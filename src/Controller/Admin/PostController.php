<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostCreateType;
use App\Form\PostUpdateType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/posts')]
#[IsGranted('ROLE_ADMIN')]
final class PostController extends AbstractController
{
    #[Route('/create/{id}', name: 'create_post', methods: ['POST', 'GET'])]
    public function create(PostRepository $postRepository, Request $request, EntityManagerInterface $entityManager, User $user): Response
    {
        $post = new Post();

        $form = $this->createForm(PostCreateType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $postRepository->saveImage($post, $imageFile);
            }

            $post->setUser($user);
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success', 'Post successfully created');

            return $this->redirectToRoute('show_user', ['id' => $post->getUser()->getId()]);
        }

        return $this->render('admin/post/create.html.twig', ['form' => $form]);
    }


    #[Route('/{id}/edit', name: 'edit_post', methods: ['POST', 'GET'])]
    public function edit(PostRepository $postRepository, EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        $post = $entityManager->find(Post::class, $id);
        $postThumbnail = $post ? $post->getThumbnail() : null;

        $form = $this->createForm(PostUpdateType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $postRepository->removeImageFiles($post);
                $postRepository->saveImage($post, $imageFile);
            }

            if ($form->get('removeFile')->getData()) {
                $postRepository->removeImageFiles($post);
                $post->setImage(null);
                $post->setThumbnail(null);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Post successfully updated');

            return $this->redirectToRoute('show_post', ['id' => $post->getId()]);
        }

        return $this->render('admin/post/edit.html.twig', ['post' => $post, 'form' => $form, 'postThumbnail' => $postThumbnail]);
    }


    #[Route('/{id}', name: 'delete_post', methods: ['DELETE'])]
    public function delete(PostRepository $postRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        $postRepository->removeImageFiles($post);

        $entityManager->remove($post);
        $entityManager->flush();
        $this->addFlash('success', 'Post successfully deleted');

        return $this->redirectToRoute('show_user', ['id' => $post->getUser()->getId()]);
    }


    #[Route('/{id}', name: 'show_post')]
    public function show(EntityManagerInterface $entityManager, $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        if ($post) {
            $commentaries = $post->getCommentaries();
        } else {
            $commentaries = null;
        }


        return $this->render('admin/post/show.html.twig', [
            'post' => $post, 'commentaries' => $commentaries
        ]);
    }
}
