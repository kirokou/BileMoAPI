<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Service\DbService;
use Swagger\Annotations as SWG;
use Swagger\Annotations\Schema;
use App\Repository\ProductRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\Security as nSecurity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/product", defaults={"_format"="json"})
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
     * Get details of a product
     * @Route("/{id}", name="app_product_show", methods={"GET"})
     *
     * @SWG\Tag(name="Product")       
     *
     * @SWG\Parameter(
     *   name="id",
     *   description="Product id",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(
     *         type = "array",
     *         @SWG\Items(ref=@Model(type=Product::class))
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="FORBIDDEN"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="NOT FOUND"
     * )
     * @nSecurity(name="Bearer")
     * 
     * @param Product $product
     * @param ProductRepository $productRepository
    */
    public function show(Product $product, ProductRepository $productRepository)
    {
        $product = $productRepository->find($product->getId());
        $data = $this->serializer->serialize($product, 'json', $this->getContext(['product_detail','default']));

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * List client's products
     * @Route("/", name="app_product_index", methods={"GET"})
     * 
     * @SWG\Tag(name="Product")
     * 
     * @SWG\Response(
     *    response=200,
     *    description="OK",
     *    @SWG\Schema(
     *       type="array",
     *       @SWG\Items(ref=@Model(type=Product::class))
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @nSecurity(name="Bearer")
     * @param  mixed $request
     * @param  mixed $productRepository
     * @param  mixed $dbService
     * @return Response
     */
    public function index(Request $request, ProductRepository $productRepository, DbService $dbService): Response
    {
        // Cache Management
        $response = new JsonResponse();
        $response->setEtag(md5($dbService->getLastUpdate('product')));
        $response->setPublic();
 
        if ($response->isNotModified($request)) {
            return $response;
        }
         
        $product = $productRepository->findAll();
        $data = $this->serializer->serialize($product,'json', $this->getContext(['product_list']));

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Create a product - Bilmo Admin Only
     * @Route("", name="app_product_new", methods={"POST"})
     * 
     * @SWG\Tag(name="Product")
     * 
     * @SWG\Response(
     *    response=200,
     *    description="OK",
     *    @SWG\Schema(
     *       type="array",
     *       @SWG\Items(ref=@Model(type=Product::class))
     *     )
     * )
     * 
     * @SWG\Parameter(
     *   name="Product",
     *   description="Fields to provide to create a new product",
     *   in="body",
     *   required=true,
     *   type="string",
     *   @SWG\Schema(
     *       title="Product fields",
     *       required={"reference", "name", "description", "price", "quantity"},
     *       @SWG\Property(property="reference", type="string", example="124ASD"),
     *       @SWG\Property(property="name", type="string", example="Iphone 7"),
     *       @SWG\Property(property="description", type="string", example="Un téléphone dernière génération à la qualité éprouvée"),
     *       @SWG\Property(property="price", type="float", example=725.53),
     *       @SWG\Property(property="quantity", type="integer", example=15)
     *     )
     * )
     * @SWG\Response(
     *     response=201,
     *     description="CREATED",
     *     @SWG\Schema(
     *         @SWG\Items(ref=@Model(type=Product::class))
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="BAD REQUEST"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="FORBIDDEN"
     * )
     * 
     * @nSecurity(name="Bearer")
     * 
     * @param  mixed $request
     * @param  mixed $validator
     * 
     * @return void
     */
    public function new(Request $request, ValidatorInterface $validator)
    {
        $product = $this->serializer->deserialize($request->getContent(), Product::class, 'json');

        $errors = $validator->validate($product);
        if(count($errors)) {
            $errors = $this->serializer->serialize($errors, 'json');
            return new Response($errors, 400, [
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
     * Update a product - Bilmo Admin Only
     * @Route("/{id}", name="app_product_update", methods={"PUT"})
     * 
     * @SWG\Tag(name="Product")
     * 
     * @SWG\Parameter(
    *   name="id",
    *   description="Product id",
    *   in="path",
    *   required=true,
    *   type="integer"
    * )
    * @SWG\Parameter(
    *   name="Product",
    *   description="Fields to provide to update a product",
    *   in="body",
    *   required=true,
    *   type="string",
    *   @SWG\Schema(
    *       title="Product fields",
    *       @SWG\Property(property="reference", type="string", example="124ASD"),
    *       @SWG\Property(property="name", type="string", example="Iphone 7"),
    *       @SWG\Property(property="description", type="string", example="Un téléphone dernière génération à la qualité éprouvée"),
    *       @SWG\Property(property="price", type="float", example=725.53),
    *       @SWG\Property(property="quantity", type="integer", example=15)
    *     )
    * )
    *
    * @SWG\Response(
    *     response=400,
    *     description="BAD REQUEST"
    * )
    * @SWG\Response(
    *     response=401,
    *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
    * )
    * @SWG\Response(
    *     response=403,
    *     description="FORBIDDEN"
    * )
    * @SWG\Response(
    *     response=404,
    *     description="NOT FOUND"
    * )
     *   @nSecurity(name="Bearer")
     * 
     * @SWG\Response(
     *    response=200,
     *    description="OK",
     *    @SWG\Schema(
     *       type="array",
     *       @SWG\Items(ref=@Model(type=Product::class))
     *     )
     * )
     * @param  mixed $request
     * @param  mixed $product
     * @param  mixed $validator
     * 
     * @return void
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
     * Delete a product - Bilmo Admin Only
     * @Route("/{id}", name="app_product_delete", methods={"DELETE"})
     * 
     * @SWG\Tag(name="Product")
     * 
     * @SWG\Parameter(
     *   name="id",
     *   description="Product id",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     *
     * @SWG\Response(
     *    response=204,
     *    description="OK",
     *    @SWG\Schema(
     *       type="array",
     *       @SWG\Items(ref=@Model(type=Product::class))
     *     )
     * )
     * 
     * @nSecurity(name="Bearer")
     * 
     * @param  mixed $product
     * 
     * @return void
     */
    public function delete(Product $product)
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return new Response(null, 204);
    }
      
    /**
     * @param  mixed $array
     */
    private function getContext(Array $array=['product_list'])
    { 
        return $this->context = SerializationContext::create()->setGroups($array);
    }
}
