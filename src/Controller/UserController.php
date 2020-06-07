<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\DbService;
use Swagger\Annotations as SWG;
use Swagger\Annotations\Schema;
use Swagger\Annotations\Property;
use App\Repository\UserRepository;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/user")
 * @IsGranted("ROLE_ADMIN", statusCode=403, message="Access Denied")
 */
class UserController extends AbstractController
{
    private $serializer;
    private $entityManager;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * Get list of user of current Client
     * @Route("/", name="app_user_index", methods={"GET"})
     * @SWG\Tag(name="User")
     * 
     * @SWG\Response(
     *    response=204,
     *    description="NO CONTENT",
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *      @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @nSecurity(name="Bearer")
     * 
     * @param  mixed $request
     * @param  mixed $userRepository
     * @param  mixed $dbService
     * @return Response
     */    
    public function index(Request $request, UserRepository $userRepository, DbService $dbService): Response
    {
        // Cache Management
        $response = new JsonResponse();
        $response->setEtag(md5($dbService->getLastUpdate('user')));
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        $user = $userRepository->findBy(['client'=>$this->getUser()->getId()], []);
        $data = $this->serializer->serialize($user,'json', $this->getContext());

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Get a current client's user details 
     * @Route("/{id}", name="app_user_show", methods={"GET"})
     *
     * @SWG\Tag(name="User")
     * 
     * @SWG\Response(
     *     response=200,
     *     description="OK"
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
     * @param  mixed $user
     * @param  mixed $userRepository
     * 
     * @return void
     * 
     */
    public function show(User $user, UserRepository $userRepository)
    {
        $user = $userRepository->findOneBy(['client'=>$this->getUser()->getId(), 'id'=>$user->getId()]);

        if (\is_null($user)) {
            $data = [
                'status' => 403,
                'message' => 'Accès non autorisé.'
            ];

            return new JsonResponse($data, 403);
        }
        
        $data = $this->serializer->serialize($user, 'json', $this->getContext(['user_detail','client_detail','default']));

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("", name="app_user_new", methods={"POST"})
     * @SWG\Tag(name="User")
     * 
     * @SWG\Response(
     *    response=204,
     *    description="NO CONTENT",
     * )
     * 
     * @SWG\Parameter(
     *   name="User",
     *   description="Fields to provide for create a new User",
     *   in="body",
     *   required=true,
     *   type="string",
     *   @SWG\Schema(
     *       title="User fields",
     *       required={"firstname", "lastname", "email"},
     *       @SWG\Property(property="firstname", type="string", example="kirikou"),
     *       @SWG\Property(property="lastname", type="string", example="toto"),
     *       @SWG\Property(property="email", type="string", example="kirikou.toto@gmail.com")
     *   )
     * )
     * @SWG\Response(
     *     response=201,
     *     description="CREATED",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class))
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
     * 
     * @SWG\Response(
     *     response=403,
     *     description="FORBIDDEN"
     * )
     * @nSecurity(name="Bearer")
     * 
     * @param  mixed $request
     * @param  mixed $validator
     * @return void
     */
    public function new(Request $request, ValidatorInterface $validator)
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        $errors = $validator->validate($user);
        if(count($errors)) {
            $errors = $this->serializer->serialize($errors, 'json');

            return new Response($errors, 400, [
                'Content-Type' => 'application/json'
            ]);
        }
        $user->setClient($this->getUser()); 

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $data = [
            'status' => 201,
            'message' => 'L\'utilisateur a bien été ajouté.'
        ];

        return new JsonResponse($data, 201);
    }

    /**
     * Delete an user
     * @Route("/{id}", name="app_user_delete", methods={"DELETE"})
     * @SWG\Tag(name="User")
     * 
     * @SWG\Response(
     *    response=204,
     *    description="DELETE IS OK",
     * )
     * 
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
     * 
     * @nSecurity(name="Bearer")
     * 
     * @param  mixed $user
     * 
     * @return void
     */
    public function delete(User $user)
    {
       // @Security("user == user.client", message="pas droit.")
       
        if($this->getUser()->getId() !== $user->getClient()->getId()) {
            $data = [
                'status' => 403,
                'message' => 'Vous ne disposez de droit de suppression sur ce client.'
            ];
    
            return new JsonResponse($data, 403);
        }
       
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new Response(null, 204);
    }
       
    /**
     * @param  mixed $array
     */
    private function getContext(Array $array=['user_list'])
    { 
        return $this->context = SerializationContext::create()->setGroups($array);
    }

}

