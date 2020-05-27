<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/client")
 */
class ClientController extends AbstractController
{
    private $serializer;
    private $entityManager;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="app_client_index", methods={"GET"})
     */
    public function index(Request $request, ClientRepository $clientRepository): Response
    {
        $client = $clientRepository->findAll();
        $data = $this->serializer->serialize($client,'json', $this->getContext());

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
    * @Route("/{id}", name="app_client_show", methods={"GET"})
    */
    public function show(Client $client, ClientRepository $clientRepository)
    {
        $client = $clientRepository->find($client->getId());
        $data = $this->serializer->serialize($client, 'json', $this->getContext(['client_detail']));

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
 
    /**
     * @param  mixed $array
     */
    private function getContext(Array $array=['client_list'])
    { 
        return $this->context = SerializationContext::create()->setGroups($array);
    }
}
