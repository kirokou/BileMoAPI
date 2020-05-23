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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{

    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
    * @Route("/{id}", name="app_product_show", methods={"GET"})
    */
    public function show(Product $product, ProductRepository $productRepository)
    {
        $product = $productRepository->find($product->getId());
        
        $data = $this->serializer->serialize($product, 'json', [
            'groups' => ['show']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/{page<\d+>?1}", name="app_product_index", methods={"GET"})
     */
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $page = $request->query->get('page');
        if (is_null($page) || $page < 1) {
            $page = 1;
        }
        
        $limit = 5;
    
        $data = $this->serializer->serialize($productRepository->findAllProduct($page, $limit), 'json', [
            'groups' => ['index']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }


    /**
     * @Route("", name="app_product_new", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $product = $this->serializer->deserialize($request->getContent(), Product::class, 'json');

        $errors = $validator->validate($product);
        if(count($errors)) {
            $errors = $this->serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }

        $entityManager->persist($product);
        $entityManager->flush();

        $data = [
            'status' => 201,
            'message' => 'Le produit a bien été ajouté.'
        ];

        return new JsonResponse($data, 201);
    }


    /**
     * @Route("/{id}", name="app_product_update", methods={"PUT"})
     */
    public function update(Request $request, Product $product, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $data = json_decode($request->getContent());
        
        foreach ($data as $key => $value) {
            if ($key && !empty($value)) {
                $name = ucfirst($key);
                $setter = 'set'.$name;
                $product->$setter($value);
            }
        }

        $errors = $validator->validate($product);
        if(count($errors)) {
            $errors = $this->serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }

        $entityManager->flush();

        $data = [
            'status' => 200,
            'message' => 'Le product a bien été mis à jour.'
        ];

        return new JsonResponse($data);
    }


    /**
     * @Route("/{id}", name="app_product_delete", methods={"DELETE"})
     */
    public function delete(Product $product, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($product);
        $entityManager->flush();

        return new Response(null, 204);
    }
}
