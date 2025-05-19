<?php
// src/Controller/RegisterController.php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface; // Import

#[Route('/api/clients')]
class RegisterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private JWTTokenManagerInterface $JWTManager // Injection de dépendance JWT
    ) {}

    // Route d'inscription
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

    // Route de récupération des clients (requiert JWT)
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

    // Route pour afficher un client spécifique (requiert JWT)
    #[Route('/{id}', name: 'client_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN or ROLE_CLIENT')]
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

    // Route de mise à jour (requiert JWT)
    #[Route('/{id}', name: 'client_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN or ROLE_CLIENT')]
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

    // Route pour supprimer un client (requiert JWT)
    #[Route('/{id}', name: 'client_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN or ROLE_CLIENT')]
    public function delete(Client $client): JsonResponse
    {
        $this->em->remove($client);
        $this->em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    // Demande de réinitialisation de mot de passe
    #[Route('/reset-password', name: 'client_reset_password_request', methods: ['POST'])]
    public function requestPasswordReset(Request $request, ClientRepository $repo, MailerService $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        if (!$email) {
            return $this->json(['error' => 'Champ Obligatoire'], Response::HTTP_BAD_REQUEST);
        }

        $client = $repo->findOneBy(['email' => $email]);
        if (!$client) {
            return $this->json(['error' => 'Compte introuvable'], Response::HTTP_NOT_FOUND);
        }

        $code = random_int(100000, 999999);
        $client->setCodeVerification((string) $code);
        $client->setDateGeneration(new \DateTime());
        $client->setStatutCompte(false);
        $this->em->flush();

        $mailer->sendEmail(
            $client->getEmail(),
            'Réinitialisation de mot de passe',
            "<p>Voici votre code de vérification : <strong>$code</strong></p>"
        );

        return $this->json(['message' => 'Code envoyé par email']);
    }

    // Confirmer la réinitialisation du mot de passe
    #[Route('/confirmer-reinitialisation', name: 'confirmer_reinit_mdp', methods: ['POST'])]
    public function confirmerReinit(Request $request, ClientRepository $repo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $code = $data['code'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (!$email || !$code || !$newPassword) {
            return $this->json(['error' => 'Champs manquants'], Response::HTTP_BAD_REQUEST);
        }

        $client = $repo->findOneBy(['email' => $email, 'codeVerification' => $code]);
        if (!$client) {
            return $this->json(['error' => 'Code incorrect'], Response::HTTP_UNAUTHORIZED);
        }

        $interval = (new \DateTime())->getTimestamp() - $client->getDateGeneration()->getTimestamp();
        if ($interval > 120) {
            return $this->json(['error' => 'Code expiré'], Response::HTTP_UNAUTHORIZED);
        }

        $client->setPassword($this->passwordHasher->hashPassword($client, $newPassword));
        $client->setCodeVerification(null);
        $client->setDateGeneration(null);
        $client->setStatutCompte(true);
        $this->em->flush();

        return $this->json(['message' => 'Mot de passe mis à jour']);
    }
}
