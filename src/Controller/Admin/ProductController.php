<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/products')]
final class ProductController extends AbstractController
{
    private const PRODUCTS_PER_PAGE = 9;

    #[Route('/', name: 'index_product', methods: ['POST', 'GET'])]
    public function index(ProductRepository $productRepository, ProductCategoryRepository $productCategoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $filter = $request->query->all();

        $sort = $request->query->get('sort', 'p.price');
        $direction = $request->query->get('direction', 'desc');

        //$minPrice = $request->query->get('minPrice');
        //$maxPrice = $request->query->get('maxPrice');
        //$colour = $request->query->get('colour');
        //$category = $request->query->get('category');

        $search = $request->query->get('search');

        $colours = $productRepository->getProductDistinctColours();
        $categories = $productCategoryRepository->findAll();

        $query = $productRepository->createSearchAndFilterBuilder($search, $sort, $direction, $filter);

        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), self::PRODUCTS_PER_PAGE);

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/product/index_search.html.twig', ['pagination' => $pagination]);
        }

        return $this->render('admin/product/index.html.twig', [
            'pagination' => $pagination,
            'colours' => $colours,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}', name: 'show_product', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('admin/product/show.html.twig', ['product' => $product]);
    }

}
