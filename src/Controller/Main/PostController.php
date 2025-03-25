<?php

namespace App\Controller\Main;

use App\Entity\Post;
use App\Form\CommentaryCreateType;
use App\Form\PostCreateType;
use App\Form\PostUpdateType;
use App\Repository\CommentaryLikeRepository;
use App\Repository\PostRepository;
use App\Service\ImageProcessing;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/posts')]
final class PostController extends AbstractController
{
    private const POSTS_PER_PAGE = 12;

    public function __construct(private ImageProcessing $imageProcessing)
    {

    }

    #[Route('/create', name: 'main_create_post', methods: ['POST', 'GET'])]
    public function create(EntityManagerInterface $entityManager, Request $request): Response
    {
        $post = new Post();
        $user = $this->getUser();

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

            return $this->redirectToRoute('main_index_post');
        }

        return $this->render('main/post/create.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/edit', name: 'main_edit_post', methods: ['POST', 'GET'])]
    #[IsGranted('POST_EDIT', subject: 'post')]
    public function edit(EntityManagerInterface $entityManager, Request $request, int $id, Post $post): Response
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

            return $this->redirectToRoute('main_show_post', ['id' => $post->getId()]);
        }

        return $this->render('main/post/edit.html.twig', ['post' => $post, 'form' => $form, 'postThumbnail' => $postThumbnail]);
    }

    #[Route('/{id}', name: 'main_delete_post', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        $this->imageProcessing->removeImage($post);

        $entityManager->remove($post);
        $entityManager->flush();
        $this->addFlash('success', 'Post successfully deleted');

        return $this->redirectToRoute('main_index_post');
    }

    #[Route('/', name: 'main_index_post')]
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $postRepository->createQueryBuilder('p')->where('p.is_published = 1');

        $search = trim($request->get('search'));

        $query = $postRepository->createSearchBuilder($search, $query);

        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), self::POSTS_PER_PAGE);

        if ($request->isXmlHttpRequest()) {
            return $this->render('main/post/index_search.html.twig', ['pagination' => $pagination]);
        }

        return $this->render('main/post/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }


    #[Route('/{id}', name: 'main_show_post')]
    public function show(PostRepository $postRepository, CommentaryLikeRepository $commentaryLikeRepository, int $id): Response
    {
        $query = $postRepository->createQueryBuilder('p')->where('p.is_published = 1', "p.id = $id")->getQuery();
        $post = $query->getOneOrNullResult();

        $commentaries = null;

        $form = $this->createForm(CommentaryCreateType::class);

        if ($post) {
            $commentaries = $post->getCommentaries();
            $likedCommentaries = [];

            foreach ($commentaries as $commentary) {
                $likedCommentaries[$commentary->getId()] = $commentaryLikeRepository->isCommentaryLikeByUser($commentary, $this->getUser());
            }
        }

        return $this->render('main/post/show.html.twig', ['post' => $post, 'commentaries' => $commentaries, 'likedCommentaries' => $likedCommentaries, 'form' => $form]);
    }
}