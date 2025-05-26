<?php
namespace App\Controller;
use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/categories')]
class CategorieController extends AbstractController
{
    // CREATE
    #[Route('/', name: 'categorie_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $categorie = new Categorie();
        $categorie->setNomCategorie($data['nomCategorie'] ?? '');

        $errors = $validator->validate($categorie);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], 400);
        }

        $em->persist($categorie);
        $em->flush();

        return $this->json($this->serializeCategorie($categorie), 201);
    }

    // READ ALL
    #[Route('/', name: 'categorie_index', methods: ['GET'])]
    public function index(CategorieRepository $repo): JsonResponse
    {
        $categories = $repo->findAll();
        return $this->json(array_map([$this, 'serializeCategorie'], $categories));
    }

    // READ ONE
    #[Route('/{id}', name: 'categorie_show', methods: ['GET'])]
    public function show(Categorie $categorie): JsonResponse
    {
        return $this->json($this->serializeCategorie($categorie, true));
    }

    // UPDATE
    #[Route('/{id}', name: 'categorie_update', methods: ['PUT'])]
    public function update(
        Categorie $categorie,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (isset($data['nomCategorie'])) {
            $categorie->setNomCategorie($data['nomCategorie']);
        }

        $errors = $validator->validate($categorie);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], 400);
        }

        $em->flush();

        return $this->json($this->serializeCategorie($categorie));
    }

    // DELETE
    #[Route('/{id}', name: 'categorie_delete', methods: ['DELETE'])]
    public function delete(
        Categorie $categorie,
        EntityManagerInterface $em
    ): JsonResponse {
        // Vérifie si la catégorie est utilisée par des produits
        if ($categorie->getProduits()->count() > 0) {
            return $this->json([
                'error' => 'Impossible de supprimer : la catégorie est associée à des produits'
            ], 422);
        }

        $em->remove($categorie);
        $em->flush();

        return $this->json(null, 204);
    }

    // Sérialisation
    private function serializeCategorie(Categorie $categorie, bool $detailed = false): array
    {
        $data = [
            'id' => $categorie->getId(),
            'nomCategorie' => $categorie->getNomCategorie()
        ];

        if ($detailed) {
            $data['produits'] = array_map(
                fn($produit) => ['id' => $produit->getId(), 'nom' => $produit->getNomProduit()],
                $categorie->getProduits()->toArray()
            );
        }

        return $data;
    }
}