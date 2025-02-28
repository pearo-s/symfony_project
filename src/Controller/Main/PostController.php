<?php

namespace App\Controller\Main;

use App\Entity\Post;
use App\Form\CommentaryCreateType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostController extends AbstractController
{
    #[Route('/posts', name: 'main_index_post')]
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $postRepository->createQueryBuilder('p')->where('p.is_published = 1');

        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), 10);

        return $this->render('main/post/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/posts/{id}', name: 'main_show_post')]
    public function show(PostRepository $postRepository, int $id)
    {
        $query = $postRepository->createQueryBuilder('p')->where('p.is_published = 1', "p.id = $id")->getQuery();//->find($id);
        $post = $query->getOneOrNullResult();
        $commentaries = null;

        $form = $this->createForm(CommentaryCreateType::class);

        if ($post) {
            $commentaries = $post->getCommentaries();
        }

        return $this->render('main/post/show.html.twig', ['post' => $post, 'commentaries' => $commentaries, 'form' => $form]);
    }
}