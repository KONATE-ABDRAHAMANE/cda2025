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
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[Route('/api')]
class RegisterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private JWTTokenManagerInterface $JWTManager
    ) {}

    #[Route('/client', name: 'client_register', methods: ['POST'])]
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

        return $this->json(['message' => 'Client inscrit avec succès'], Response::HTTP_CREATED);
    }

    #[Route('/employe', name: 'create_user', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $role = strtoupper($data['role'] ?? '');

        if (!in_array("ROLE_$role", ['ROLE_ADMIN', 'ROLE_LIVREUR'])) {
            return $this->json(['error' => 'Rôle non autorisé'], 400);
        }

        $client = new Client();
        $client->setEmail($data['email'] ?? '');
        $client->setNom($data['nom'] ?? '');
        $client->setPrenom($data['prenom'] ?? '');
        $client->setTelephone($data['telephone'] ?? '');
        $client->setStatutCompte(true);
        $client->setRoles(["ROLE_$role"]);

        if (isset($data['password'])) {
            $client->setPassword(
                $this->passwordHasher->hashPassword($client, $data['password'])
            );
        }

        $errors = $this->validator->validate($client);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->persist($client);
        $this->em->flush();

        return $this->json(['message' => "$role créé avec succès"]);
    }

    #[Route('/utilisateurs', name: 'user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, ClientRepository $repo): JsonResponse
    {
        $roleFilter = strtoupper($request->query->get('role', ''));

        if ($roleFilter === 'CLIENT') {
            $users = $repo->findByRole('ROLE_CLIENT');
        } elseif ($roleFilter === 'ADMIN_LIVREUR') {
            $users = $repo->findByRoles(['ROLE_ADMIN', 'ROLE_LIVREUR']);
        } elseif ($roleFilter === 'ADMIN') {
            $users = $repo->findByRoles('ROLE_ADMIN');
        } elseif ($roleFilter === 'LIVREUR') {
            $users = $repo->findByRoles('ROLE_LIVREUR');
        } else {
            return $this->json(['error' => 'Paramètre "role" requis (CLIENT ou ADMIN_LIVREUR)'], 400);
        }

        $data = array_map(fn(Client $u) => [
            'id' => $u->getId(),
            'email' => $u->getEmail(),
            'nom' => $u->getNom(),
            'prenom' => $u->getPrenom(),
            'roles' => $u->getRoles(),
            'statutCompte' => $u->isStatutCompte(),
        ], $users);

        return $this->json($data);
    }

    #[Route('/utilisateur/{id}', name: 'user_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN or ROLE_CLIENT')]
    public function updateUser(Request $request, Client $client): JsonResponse
    {
        $user = $this->getUser();

        if (in_array('ROLE_CLIENT', $user->getRoles()) && $user->getId() !== $client->getId()) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['email'])) $client->setEmail($data['email']);
        if (isset($data['nom'])) $client->setNom($data['nom']);
        if (isset($data['prenom'])) $client->setPrenom($data['prenom']);
        if (isset($data['telephone'])) $client->setTelephone($data['telephone']);
        if (isset($data['statutCompte']) && $this->isGranted('ROLE_ADMIN')) $client->setStatutCompte($data['statutCompte']);
        if (isset($data['roles']) && $this->isGranted('ROLE_ADMIN')) $client->setRoles($data['roles']);

        if (isset($data['password'])) {
            $client->setPassword(
                $this->passwordHasher->hashPassword($client, $data['password'])
            );
        }

        $this->em->flush();
        return $this->json(['message' => 'Utilisateur modifié']);
    }

    #[Route('/utilisateur/{id}', name: 'user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN or ROLE_CLIENT')]
    public function delete(Client $client): JsonResponse
    {
        $user = $this->getUser();
        if (in_array('ROLE_CLIENT', $user->getRoles()) && $user->getId() !== $client->getId()) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        $this->em->remove($client);
        $this->em->flush();
        return $this->json(null, 204);
    }

    #[Route('/utilisateur/{id}', name: 'client_show', methods: ['GET'])]
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

    #[Route('/utilisateur/demande-reinitialisation', name: 'client_reset_password_request', methods: ['POST'])]
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

    #[Route('/utilisateur/confirmer-reinitialisation', name: 'confirmer_reinit_mdp', methods: ['POST'])]
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

