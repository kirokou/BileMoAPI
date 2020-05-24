<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/product")
 */
class ProductController extends AbstractController
{

    private $serializer;
    private $entityManager;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
    * @Route("/{id}", name="app_product_show", methods={"GET"})
    */
    public function show(Product $product, ProductRepository $productRepository)
    {
        $product = $productRepository->find($product->getId());
        $data = $this->serializer->serialize($product, 'json', $this->getContext('detail'));

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/", name="app_product_index", methods={"GET"})
     */
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findAll();
        $data = $this->serializer->serialize($product,'json', $this->getContext());

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("", name="app_product_new", methods={"POST"})
     */
    public function new(Request $request, ValidatorInterface $validator)
    {
        $product = $this->serializer->deserialize($request->getContent(), Product::class, 'json');

        $errors = $validator->validate($product);
        if(count($errors)) {
            $errors = $this->serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $data = [
            'status' => 201,
            'message' => 'Le produit a bien été ajouté.'
        ];

        return new JsonResponse($data, 201);
    }


    /**
     * @Route("/{id}", name="app_product_update", methods={"PUT"})
     */
    public function update(Request $request, Product $product, ValidatorInterface $validator)
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

        $this->entityManager->flush();

        $data = [
            'status' => 200,
            'message' => 'Le product a bien été mis à jour.'
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/{id}", name="app_product_delete", methods={"DELETE"})
     */
    public function delete(Product $product)
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return new Response(null, 204);
    }
    
    /**
     * @param  mixed $context
     */
    private function getContext(String $context='list')
    { 
        return $this->context = SerializationContext::create()->setGroups(array($context));
    }
}
