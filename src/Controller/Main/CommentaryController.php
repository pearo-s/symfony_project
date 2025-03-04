<?php

namespace App\Controller\Main;

use App\Entity\Commentary;
use App\Entity\Post;
use App\Form\CommentaryCreateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentaryController extends AbstractController
{
    #[Route('/posts/{id}/commentary', name: 'create_commentary', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        $commentary = new Commentary();
        $user = $this->getUser();

        $post = $entityManager->getRepository(Post::class)->find($id);

        $form = $this->createForm(CommentaryCreateType::class, $commentary);
        $form->handleRequest($request);

        if ($user) {
            if ($form->isSubmitted() && $form->isValid()) {
                $commentary->setUser($user);
                $commentary->setPost($post);

                if (in_array('ROLE_ADMIN', $user->getRoles())) {
                    $commentary->setIsModerated(1);
                    $this->addFlash('success', 'Your commentary has been successfully added.');
                }    else {
                    $this->addFlash('success', 'Your commentary will appear after moderation');
                }

                $entityManager->persist($commentary);
                $entityManager->flush();
            }
        }


        $referer = $request->headers->get('referer');

        return $this->redirect($referer ?? $this->generateUrl('main_show_post', ['id' => $id]));
    }


    #[Route('/commentaries/{id}', name: 'confirm_commentary', methods: ['POST'])]
    public function confirm(EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        $commentary = $entityManager->getRepository(Commentary::class)->find($id);

        $referer = $request->headers->get('referer');

        if (!$commentary) {
            $this->addFlash('error', 'Commentary not found');
            return $this->redirect($referer ?? $this->generateUrl('index_user'));
        }

        $commentary->setIsModerated(1);
        $entityManager->flush();
        $this->addFlash('success', 'Commentary successfully confirmed');

        return $this->redirect($referer ?? $this->generateUrl('index_user'));
    }


    #[Route('/commentaries/{id}', name: 'delete_commentary', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        $commentary = $entityManager->getRepository(Commentary::class)->find($id);
        $entityManager->remove($commentary);
        $entityManager->flush();
        $this->addFlash('success', 'Commentary successfully deleted');

        $referer = $request->headers->get('referer');//prev page

        return $this->redirect($referer ?? $this->generateUrl('index_user'));
    }
}