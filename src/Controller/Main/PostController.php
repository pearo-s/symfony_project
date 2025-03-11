<?php

namespace App\Controller\Main;

use App\Entity\Post;
use App\Form\CommentaryCreateType;
use App\Form\PostCreateType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/posts')]
final class PostController extends AbstractController
{
    #[Route('/create', name: 'main_create_post', methods: ['POST', 'GET'])]
    public function create(PostRepository $postRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $post = new Post();
        $user = $this->getUser();

        $form = $this->createForm(PostCreateType::class, $post);
        $form->handleRequest($request);

        $uploadFile = $this->getParameter('post_image_directory');

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $postRepository->saveImage($uploadFile, $post, $imageFile);
            }

            $post->setUser($user);
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success', 'Post successfully created');

            return $this->redirectToRoute('main_index_post');
        }

        return $this->render('main/post/create.html.twig', ['form' => $form]);
    }


    #[Route('/', name: 'main_index_post')]
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $postRepository->createQueryBuilder('p')->where('p.is_published = 1');

        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), 12);

        return $this->render('main/post/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }


    #[Route('/{id}', name: 'main_show_post')]
    public function show(PostRepository $postRepository, int $id): Response
    {
        $query = $postRepository->createQueryBuilder('p')->where('p.is_published = 1', "p.id = $id")->getQuery();
        $post = $query->getOneOrNullResult();
        $commentaries = null;

        $form = $this->createForm(CommentaryCreateType::class);

        if ($post) {
            $commentaries = $post->getCommentaries();
        }

        return $this->render('main/post/show.html.twig', ['post' => $post, 'commentaries' => $commentaries, 'form' => $form]);
    }
}