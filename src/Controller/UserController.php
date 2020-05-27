<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    * @Route("/", name="app_user_index", methods={"GET"})
    */
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $user = $userRepository->findBy(['client'=>$this->getUser()->getId()], []);
        $data = $this->serializer->serialize($user,'json', $this->getContext());

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
    * @Route("/{id}", name="app_user_show", methods={"GET"})
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
     */
    public function new(Request $request, ValidatorInterface $validator)
    {
        $user = $this->serializer->deserialize($request->getContent(), user::class, 'json');

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
     * @Route("/{id}", name="app_user_delete", methods={"DELETE"})
     * 
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
