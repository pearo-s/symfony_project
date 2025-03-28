<?php

namespace App\Controller\Main;

use App\Entity\Product;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(ProductRepository $productRepository, ProductCategoryRepository $productCategoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $requestData = $request->query->all();

        $colours = $productRepository->getProductDistinctColours();
        $categories = $productCategoryRepository->findAll();

        $query = $productRepository->createSearchAndFilterBuilder($requestData);

        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), self::PRODUCTS_PER_PAGE);

        if ($request->isXmlHttpRequest()) {
            return $this->render('main/product/index_search.html.twig', ['pagination' => $pagination]);
        }

        return $this->render('main/product/index.html.twig', [
            'pagination' => $pagination,
            'colours' => $colours,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}', name: 'main_show_product', methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        return $this->render('main/product/show.html.twig', ['product' => $product]);
    }
}
