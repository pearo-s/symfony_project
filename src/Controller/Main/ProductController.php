<?php

namespace App\Controller\Main;

use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products')]
final class ProductController extends AbstractController
{
    private const PRODUCTS_PER_PAGE = 9;

    #[Route('/', name: 'main_index_product')]
    public function index(ProductRepository $productRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $productRepository->createQueryBuilder('p');

        $search = $request->query->get('search');

        $query = $productRepository->createSearchBuilder($search, $query);

        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), self::PRODUCTS_PER_PAGE);

        if ($request->isXmlHttpRequest()) {
            return $this->render('main/product/index_search.html.twig', ['pagination' => $pagination]);
        }

        return $this->render('main/product/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
