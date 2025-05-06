<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/clients')]
class registerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'client_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $client = new Client();
        $client->setEmail($data['email'] ?? '');
        $client->setNom($data['nom'] ?? '');
        $client->setPrenom($data['prenom'] ?? '');
        $client->setTelephone($data['telephone'] ?? '');
        $client->setStatutCompte(true);
        $client->setRoles(['ROLE_CLIENT']);

        if (isset($data['password'])) {
            $client->setPassword(
                $this->passwordHasher->hashPassword($client, $data['password'])
            );
        }

        $errors = $this->validator->validate($client);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($client);
        $this->em->flush();

        return $this->json(['message' => 'Client registered'], Response::HTTP_CREATED);
    }

    #[Route('/admin', name: 'admin_create_user', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createByAdmin(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $client = new Client();
        $client->setEmail($data['email'] ?? '');
        $client->setNom($data['nom'] ?? '');
        $client->setPrenom($data['prenom'] ?? '');
        $client->setTelephone($data['telephone'] ?? '');
        $client->setStatutCompte(true);

        $allowedRoles = ['ROLE_ADMIN', 'ROLE_LIVREUR'];
        $roles = array_intersect($data['roles'] ?? [], $allowedRoles);
        $client->setRoles($roles ?: ['ROLE_CLIENT']);

        if (isset($data['password'])) {
            $client->setPassword(
                $this->passwordHasher->hashPassword($client, $data['password'])
            );
        }

        $errors = $this->validator->validate($client);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($client);
        $this->em->flush();

        return $this->json(['message' => 'User created by admin'], Response::HTTP_CREATED);
    }

    #[Route('', name: 'client_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(ClientRepository $repo): JsonResponse
    {
        $clients = $repo->findAll();

        $data = array_map(fn(Client $c) => [
            'id' => $c->getId(),
            'email' => $c->getEmail(),
            'nom' => $c->getNom(),
            'prenom' => $c->getPrenom(),
            'roles' => $c->getRoles(),
            'statutCompte' => $c->isStatutCompte(),
        ], $clients);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'client_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Client $client): JsonResponse
    {
        return $this->json([
            'id' => $client->getId(),
            'email' => $client->getEmail(),
            'nom' => $client->getNom(),
            'prenom' => $client->getPrenom(),
            'roles' => $client->getRoles(),
            'statutCompte' => $client->isStatutCompte(),
        ]);
    }

    #[Route('/{id}', name: 'client_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(Request $request, Client $client): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) $client->setEmail($data['email']);
        if (isset($data['nom'])) $client->setNom($data['nom']);
        if (isset($data['prenom'])) $client->setPrenom($data['prenom']);
        if (isset($data['telephone'])) $client->setTelephone($data['telephone']);
        if (isset($data['statutCompte'])) $client->setStatutCompte($data['statutCompte']);
        if (isset($data['roles'])) $client->setRoles($data['roles']);
        if (isset($data['password'])) {
            $client->setPassword(
                $this->passwordHasher->hashPassword($client, $data['password'])
            );
        }

        $this->em->flush();

        return $this->json(['message' => 'Client updated']);
    }

    #[Route('/{id}', name: 'client_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Client $client): JsonResponse
    {
        $this->em->remove($client);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
