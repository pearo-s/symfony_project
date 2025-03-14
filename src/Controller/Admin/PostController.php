<?php

namespace App\Controller\Admin;

use App\Entity\Post;
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
    #[Route('/create', name: 'create_post', methods: ['POST', 'GET'])]
    public function create(PostRepository $postRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $user = $this->getUser();

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

        $form = $this->createForm(PostUpdateType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $postRepository->removeImageFiles($post);
                $postRepository->saveImage($post, $imageFile);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Post successfully updated');

            return $this->redirectToRoute('show_post', ['id' => $post->getId()]);
        }

        return $this->render('admin/post/edit.html.twig', ['form' => $form]);
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
