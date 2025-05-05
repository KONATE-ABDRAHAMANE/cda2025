<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Employe;
use App\Repository\ClientRepository;
use App\Repository\EmployeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/clients')]
class RegisterController extends AbstractController
{
    public function __construct(
        private ClientRepository $clientRepository,
        private EmployeRepository $employeRepository,
        private SerializerInterface $serializer,
        private JWTTokenManagerInterface $JWTManager,
        private EntityManagerInterface $em
    ) {}

    /* ########################
       ## AUTHENTIFICATION ##
       ######################## */

    #[Route('/connexion', name: 'client_auth_login', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password']) || !isset($data['userType'])) {
            return $this->json([
                'error' => 'Email, mot de passe et type d\'utilisateur (client|employe) requis'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = match($data['userType']) {
            'client' => $this->clientRepository->findOneBy(['email' => $data['email']]),
            'employe' => $this->employeRepository->findOneBy(['email' => $data['email']]),
            default => null
        };

        if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Identifiants invalides'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if ($user instanceof Client && !$user->isStatutCompte()) {
            return $this->json([
                'error' => 'Compte non activé',
                'code_verification' => $user->getCodeVerification()
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        return $this->json([
            'token' => $this->JWTManager->create($user),
            'user' => $this->serializeUser($user),
            'user_type' => $data['userType']
        ]);
    }

    /* ########################
       ## INSCRIPTION CLIENT ##
       ######################## */

    #[Route('/inscription', name: 'client_auth_register', methods: ['POST'])]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if ($this->clientRepository->findOneBy(['email' => $data['email'] ?? ''])) {
            return $this->json(['error' => 'Un compte existe déjà avec cet email'], JsonResponse::HTTP_CONFLICT);
        }

        $client = (new Client())
            ->setEmail($data['email'] ?? '')
            ->setPassword('') // Temporaire avant hash
            ->setNom($data['nom'] ?? '')
            ->setPrenom($data['prenom'] ?? '')
            ->setTelephone($data['telephone'] ?? null)
            ->setStatutCompte(false)
            ->setCodeVerification($this->generateVerificationCode());

        $client->setPassword($passwordHasher->hashPassword($client, $data['password'] ?? ''));

        $errors = $validator->validate($client);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->em->persist($client);
        $this->em->flush();

        return $this->json($this->serializeUser($client), JsonResponse::HTTP_CREATED);
    }

    /* ########################
       ## GESTION PROFIL ##
       ######################## */

    #[Route('/mon-profil', name: 'client_auth_profile', methods: ['GET'])]
    public function getProfile(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof Client) {
            return $this->json(['error' => 'Non authentifié'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $this->json($this->serializeUser($user));
    }

    #[Route('/mon-profil/modifier', name: 'client_auth_update', methods: ['PUT'])]
    public function updateProfile(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        /** @var Client $client */
        $client = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (isset($data['email']) && $data['email'] !== $client->getEmail()) {
            if ($this->clientRepository->findOneBy(['email' => $data['email']])) {
                return $this->json(['error' => 'Email déjà utilisé'], JsonResponse::HTTP_CONFLICT);
            }
            $client->setEmail($data['email']);
        }

        if (isset($data['nom'])) $client->setNom($data['nom']);
        if (isset($data['prenom'])) $client->setPrenom($data['prenom']);
        if (isset($data['telephone'])) $client->setTelephone($data['telephone']);
    

        $this->em->flush();

        return $this->json($this->serializeUser($client));
    }

    /* ########################
       ## MOT DE PASSE ##
       ######################## */

    #[Route('/reinitialiser-mot-de-passe', name: 'client_auth_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $required = ['email', 'code_verification', 'new_password'];

        foreach ($required as $field) {
            if (empty($data[$field] ?? null)) {
                return $this->json(['error' => "Le champ $field est requis"], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $client = $this->clientRepository->findOneBy(['email' => $data['email']]);
        if (!$client) {
            return $this->json(['error' => 'Client non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($client->getCodeVerification() !== $data['code_verification']) {
            return $this->json(['error' => 'Code de vérification invalide'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $client->setStatutCompte(true)
               ->setCodeVerification(null);

        $this->em->flush();

        return $this->json(['message' => 'Mot de passe réinitialisé avec succès']);
    }

    /* ########################
       ## UTILITAIRES ##
       ######################## */

    private function generateVerificationCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function serializeUser($user): array
    {
        return $this->serializer->normalize($user, null, [
            'groups' => $user instanceof Client ? 'client' : 'employe'
        ]);
    }
}