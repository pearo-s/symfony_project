<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ProductCreateType;
use App\Form\ProductUpdateType;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Service\ImageProcessing;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/products')]
final class ProductController extends AbstractController
{
    private const PRODUCTS_PER_PAGE = 9;

    public function __construct(private readonly ImageProcessing $imageProcessing)
    {
    }

    #[Route('/', name: 'index_product', methods: ['POST', 'GET'])]
    public function index(ProductRepository $productRepository, ProductCategoryRepository $productCategoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $requestData = $request->query->all();

        $colours = $productRepository->getProductDistinctColours();
        $categories = $productCategoryRepository->findAll();

        $query = $productRepository->createSearchAndFilterBuilder($requestData);

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

    #[Route('/create', name: 'create_product', methods: ['POST', 'GET'])]
    public function create(EntityManagerInterface $entityManager, Request $request): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductCreateType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('images')->getData();

            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $productImage = new ProductImage();
                    $productImage->setProduct($product);
                    $this->imageProcessing->save($productImage, $imageFile, 'product');
                    $entityManager->persist($productImage);
                }

                $entityManager->persist($product);
                $entityManager->flush();

                $this->addFlash('success', 'Product successfully created');

                return $this->redirectToRoute('show_product', ['id' => $product->getId()]);
            }
        }

        return $this->render('admin/product/create.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/edit', name: 'edit_product', methods: ['POST', 'GET'])]
    public function edit(EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        $productImages = $product ? $product->getProductImages() : null;

        $form = $this->createForm(ProductUpdateType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('images')->getData();

            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $productImage = new ProductImage();
                    $productImage->setProduct($product);
                    $this->imageProcessing->save($productImage, $imageFile, 'product');
                    $entityManager->persist($productImage);
                }
            }

            $removeFiles = $request->request->all('removeFiles') ?? [];

            if ($removeFiles) {
                foreach ($removeFiles as $productImageId) {
                    $productImage = $entityManager->getRepository(ProductImage::class)->find($productImageId);
                    $this->imageProcessing->removeProductImage($productImage);
                    $entityManager->remove($productImage);
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Product successfully updated');

            return $this->redirectToRoute('show_product', ['id' => $product->getId()]);
        }

        return $this->render('admin/product/edit.html.twig', ['form' => $form, 'product' => $product, 'productImages' => $productImages]);
    }

    #[Route('/{id}', name: 'delete_product', methods: ['DELETE'])]
    public function delete(Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($product->getProductImages()) {
            foreach ($product->getProductImages() as $productImage) {
                $this->imageProcessing->removeProductImage($productImage);
            }
        }

        $entityManager->remove($product);
        $entityManager->flush();
        $this->addFlash('success', 'Product successfully deleted');

        return $this->redirectToRoute('index_product');
    }

    #[Route('/{id}', name: 'show_product', methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        return $this->render('admin/product/show.html.twig', ['product' => $product]);
    }
}
