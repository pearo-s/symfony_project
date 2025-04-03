<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostCreateType;
use App\Form\PostUpdateType;
use App\Service\ImageProcessing;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/posts')]
final class PostController extends AbstractController
{
    public function __construct(private ImageProcessing $imageProcessing)
    {

    }
    #[Route('/create/{id}', name: 'create_post', methods: ['POST', 'GET'])]
    public function create(Request $request, EntityManagerInterface $entityManager, User $user): Response
    {
        $post = new Post();

        $form = $this->createForm(PostCreateType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $this->imageProcessing->save($post, $imageFile, 'post');
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
    public function edit(EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        $post = $entityManager->find(Post::class, $id);
        $postThumbnail = $post ? $post->getThumbnail() : null;

        $form = $this->createForm(PostUpdateType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $this->imageProcessing->removeImage($post);
                $this->imageProcessing->save($post, $imageFile, 'post');
            }

            if ($form->get('removeFile')->getData()) {
                $this->imageProcessing->removeImage($post);
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
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        $this->imageProcessing->removeImage($post);

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
