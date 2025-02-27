<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Form\PostCreateType;
use App\Form\PostUpdateType;
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
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();

        $form = $this->createForm(PostCreateType::class, $post);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success', 'Post successfully created');

            return $this->redirectToRoute('show_user', ['id' => $post->getUser()->getId()]);
        }

        return $this->render('post/create.html.twig', ['form' => $form]);
    }


    #[Route('/{id}/edit', name: 'edit_post', methods: ['POST', 'GET'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $post = $entityManager->find(Post::class, $id);

        $form = $this->createForm(PostUpdateType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Post successfully updated');

            return $this->redirectToRoute('show_post', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', ['form' => $form]);
    }


    #[Route('/{id}', name: 'delete_post', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        $entityManager->remove($post);
        $entityManager->flush();
        $this->addFlash('success', 'Post successfully deleted');

        return $this->redirectToRoute('show_user', ['id' => $post->getUser()->getId()]);
    }


    #[Route('/{id}', name: 'show_post')]
    public function show(EntityManagerInterface $entityManager, $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }
}
