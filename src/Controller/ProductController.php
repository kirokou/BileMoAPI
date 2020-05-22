<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
     /**
     * @Route("/{id}", name="app_product_show", methods={"GET"})
     */
    public function show(Product $product, ProductRepository $productRepository, SerializerInterface $serializer)
    {
        $product = $productRepository->find($product->getId());
        
        $data = $serializer->serialize($product, 'json', [
            'groups' => ['show']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/{page<\d+>?1}", name="app_product_index", methods={"GET"})
     */
    public function index(Request $request, ProductRepository $productRepository, SerializerInterface $serializer): Response
    {
        $page = $request->query->get('page');
        if (is_null($page) || $page < 1) {
            $page = 1;
        }
        
        $limit = 10;
    
        $data = $serializer->serialize($productRepository->findAllProduct($page,$limit), 'json',[
            'groups' => ['index']
        ]);

        return new Response($data,200,[
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("", name="app_product_new", methods={"POST"})
     */
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        $product = $serializer->deserialize($request->getContent(), Product::class, 'json');
       
        $entityManager->persist($product);
        $entityManager->flush();

        $data = [
            'status' => 201,
            'message' => 'Le produit a bien été ajouté.'
        ];

        return new JsonResponse($data, 201);
    }
}
