<?php
// src/Controller/ProduitController.php
namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/produits')]
class ProduitController extends AbstractController
{
    // CREATE
    #[Route('/ajout', name: 'produit_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $produit = new Produit();
        $produit->setNomProduit($data['nomProduit']);
        $produit->setDescription($data['description'] ?? null);
        $produit->setPrix($data['prix']);
        $produit->setStock($data['stock'] ?? 0);

        // Validation
        $errors = $validator->validate($produit);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], 400);
        }

        $em->persist($produit);
        $em->flush();

        return $this->json($this->serializeProduit($produit), 201);
    }

    // READ ALL
    #[Route('/', name: 'produit_index', methods: ['GET'])]
    public function index(ProduitRepository $repo): JsonResponse
    {
        $produits = $repo->findAll();
        return $this->json(array_map([$this, 'serializeProduit'], $produits));
    }

    // READ ONE
    #[Route('/{id}', name: 'produit_show', methods: ['GET'])]
    public function show(Produit $produit): JsonResponse
    {
        return $this->json($this->serializeProduit($produit));
    }

    // UPDATE
    #[Route('/{id}', name: 'produit_update', methods: ['PUT'])]
    
    public function update(
        Produit $produit,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (isset($data['nomProduit'])) $produit->setNomProduit($data['nomProduit']);
        if (isset($data['description'])) $produit->setDescription($data['description']);
        if (isset($data['prix'])) $produit->setPrix($data['prix']);
        if (isset($data['stock'])) $produit->setStock($data['stock']);

        $errors = $validator->validate($produit);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], 400);
        }

        $em->flush();

        return $this->json($this->serializeProduit($produit));
    }

    // DELETE
    #[Route('/{id}', name: 'produit_delete', methods: ['DELETE'])]
    public function delete(Produit $produit, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($produit);
        $em->flush();
        return $this->json(null, 204);
    }

    // Sérialisation simplifiée avec promo et prix réduit
    private function serializeProduit(Produit $produit): array
    {
        $promotion = $produit->getPromotion();

        // Calcul du prix avec réduction si promo valide
        $prixReduction = null;
        if ($promotion !== null) {
            $now = new \DateTime();
            if ($promotion->getDebut() <= $now && $promotion->getFin() >= $now) {
                $reduction = $promotion->getReduction();
                $prixReduction = round($produit->getPrix() - (($produit->getPrix() * $promotion->getReduction()) / 100), 2);

            }
        }

        return [
            'id' => $produit->getId(),
            'nomProduit' => $produit->getNomProduit(),
            'description' => $produit->getDescription(),
            'prix' => $produit->getPrix(),
            'prixReduction' => $prixReduction,
            'stock' => $produit->getStock(),
            'categorie' => $produit->getCategorie()?->getId(),
            'promotion' => $promotion ? [
                'id' => $promotion->getId(),
                'titre' => $promotion->getTitre(),
                'debut' => $promotion->getDebut()->format('Y-m-d'),
                'fin' => $promotion->getFin()->format('Y-m-d'),
                'reduction' => $promotion->getReduction()
            ] : null,
            'images' => array_map(fn($image) => $image->getLien(), $produit->getImage()->toArray()),
        ];
    }
}
