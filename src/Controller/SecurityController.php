<?php

namespace App\Controller;

use App\Entity\Client;
use Swagger\Annotations as SWG;
use Swagger\Annotations\Schema;
use Swagger\Annotations\Property;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Security as nSecurity;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
* @Route("/api")
*/
class SecurityController extends AbstractController
{
    private $serializer;
    private $validator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }
    
    /**
     * Only Bilmo Admin can register client
     * @Route("/register", name="register", methods={"POST"})
     * 
     * @SWG\Tag(name="Authentication")
     * 
     * @SWG\Parameter(
     *   name="Register",
     *   description="Register",
     *   in="body",
     *   required=true,
     *   type="string",
     *   @SWG\Schema(
     *     type="object",
     *     title="Authentication field",
     *     @SWG\Property(property="email", type="string"),
     *     @SWG\Property(property="password", type="string"),
     *     @SWG\Property(property="name", type="string")
     *     )
     * )
     * 
     * @SWG\Response(
     *    response=204,
     *    description="OK",
     * )
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager)
    {
        $values = json_decode($request->getContent());

        //Validations
        $client = new Client();
        $client->setEmail($values->email);
        $client->setPassword($values->password);
        $client->setName($values->name);

        $errors = $this->validator->validate($client);
        if(count($errors)) {
            $errors = $this->serializer->serialize($errors, 'json');

            return new Response($errors, 400, [
                'Content-Type' => 'application/json'
            ]);
        }

        $client->setPassword($passwordEncoder->encodePassword($client, $values->password));
        $client->setRoles(['ROLE_USER']);
        $entityManager->persist($client);
        $entityManager->flush();

        $data = [
            'status' => 201,
            'message' => 'L\'utilisateur a été créé'
        ];

        return new JsonResponse($data, 201);
    }

    /**
     * Get app token for sign in and query API
     * 
     * @Route("/login_check", name="login", methods={"POST"})
     * 
     * @SWG\Tag(name="Authentication")
     * 
     * @SWG\Parameter(
     *   name="authToken",
     *   description="Fields typing data to get an AuthToken",
     *   in="body",
     *   required=true,
     *   type="string",
     *   @SWG\Schema(
     *     type="object",
     *     title="Authentication field",
     *     @SWG\Property(property="username", type="string"),
     *     @SWG\Property(property="password", type="string")
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(
     *      type="string",
     *      title="Token",
     *      @SWG\Property(property="token", type="string"),
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=400,
     *     description="Bad request - Invalid JSON",
     * )
     * 
     * @SWG\Response(
     *     response=401,
     *     description="Bad credentials"
     * )
     * 
     */
    public function login(Request $request)
    {
        $user = $this->getUser();
        return $this->json([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles()
        ]);
    }
    
}
