<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Repository\EmployeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/employes')]
class EmployeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        // Le constructeur est correct, ne pas fermer la classe ici
    }

    #[Route('/login', name: 'api_employe_login', methods: ['POST'])]
    public function login(
        Request $request,
        JWTTokenManagerInterface $JWTManager,
        UserPasswordHasherInterface $passwordHasher,
        EmployeRepository $employeRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json([
                'message' => 'Email et mot de passe requis'
            ], Response::HTTP_BAD_REQUEST);
        }

        $employe = $employeRepository->findOneBy(['email' => $data['email']]);

        if (!$employe || !$passwordHasher->isPasswordValid($employe, $data['password'])) {
            return $this->json([
                'message' => 'Identifiants invalides'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $JWTManager->create($employe);

        return $this->json([
            'token' => $token,
            'user' => $this->serializeEmploye($employe)
        ]);
    }

    #[Route('/current', name: 'api_employe_current', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function current(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof Employe) {
            return $this->json(['message' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json($this->serializeEmploye($user));
    }

    #[Route('', name: 'api_employe_index', methods: ['GET'])]
    public function index(EmployeRepository $employeRepository): JsonResponse
    {
        $employes = $employeRepository->findAll();
        return $this->json(array_map([$this, 'serializeEmploye'], $employes));
    }

    #[Route('', name: 'api_employe_create', methods: ['POST'])]
    public function create(Request $request, EmployeRepository $employeRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifie si l'email est déjà utilisé
        if ($employeRepository->findOneBy(['email' => $data['email'] ?? null])) {
            return $this->json(['error' => 'Email déjà utilisé.'], Response::HTTP_CONFLICT);
        }

        $employe = new Employe();
        $employe->setEmail($data['email'] ?? '');
        $employe->setNomEmploye($data['nomEmploye'] ?? '');
        $employe->setPrenomEmploye($data['prenomEmploye'] ?? '');
        $employe->setRoles($data['roles'] ?? ['ROLE_USER']);

        if (isset($data['password'])) {
            $employe->setPassword(
                $this->passwordHasher->hashPassword($employe, $data['password'])
            );
        }

        $errors = $this->validator->validate($employe);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($employe);
        $this->entityManager->flush();

        return $this->json($this->serializeEmploye($employe), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_employe_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Employe $employe): JsonResponse
    {
        return $this->json($this->serializeEmploye($employe));
    }

    #[Route('/{id}', name: 'api_employe_update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, Employe $employe): JsonResponse
    {
        $currentUser = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN') && $currentUser !== $employe) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $employe->setEmail($data['email']);
        }
        if (isset($data['nomEmploye'])) {
            $employe->setNomEmploye($data['nomEmploye']);
        }
        if (isset($data['prenomEmploye'])) {
            $employe->setPrenomEmploye($data['prenomEmploye']);
        }
        if (isset($data['roles']) && $this->isGranted('ROLE_ADMIN')) {
            $employe->setRoles($data['roles']);
        }
        if (isset($data['password'])) {
            $employe->setPassword(
                $this->passwordHasher->hashPassword($employe, $data['password'])
            );
        }

        $errors = $this->validator->validate($employe);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json($this->serializeEmploye($employe));
    }

    #[Route('/{id}', name: 'api_employe_delete', methods: ['DELETE'])]
   
    public function delete(Employe $employe): JsonResponse
    {
        $this->entityManager->remove($employe);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function serializeEmploye(Employe $employe): array
    {
        return [
            'id' => $employe->getId(),
            'email' => $employe->getEmail(),
            'roles' => $employe->getRoles(),
            'nomEmploye' => $employe->getNomEmploye(),
            'prenomEmploye' => $employe->getPrenomEmploye(),
            'commandes' => array_map(fn($c) => $c->getId(), $employe->getCommandes()->toArray())
        ];
    }
}
