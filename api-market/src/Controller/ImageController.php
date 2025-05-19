<?php

namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/images')]
class ImageController extends AbstractController
{
    //  Ajouter des image
    #[Route('/ajout', name: 'image_ajout', methods: ['POST'])]
public function ajouterImages(
    Request $request,
    EntityManagerInterface $em,
    ProduitRepository $produitRepo,
    ImageRepository $imageRepo
): JsonResponse {
    $data = json_decode($request->getContent(), true);
    $idProduit = $data['idProduit'] ?? null;
    $liens = $data['images'] ?? [];

    if (!$idProduit || !is_array($liens)) {
        return $this->json(['error' => 'Paramètres invalides'], 400);
    }

    $produit = $produitRepo->find($idProduit);
    if (!$produit) {
        return $this->json(['error' => 'Produit introuvable'], 404);
    }

    $ajoutees = [];
    $ignorees = [];

    foreach ($liens as $lien) {
        // Vérifie si une image avec ce lien existe déjà pour ce produit
        $existing = $imageRepo->findOneBy(['lien' => $lien, 'produit' => $produit]);
        if ($existing) {
            $ignorees[] = $lien;
            continue;
        }

        $image = new Image();
        $image->setLien($lien);
        $image->setProduit($produit);
        $em->persist($image);
        $ajoutees[] = $lien;
    }

    $em->flush();

    return $this->json([
        'message' => 'Opération terminée',
        'ajoutees' => $ajoutees,
        'ignorees' => $ignorees
    ], 201);
}
    // Lire toutes les images d’un produit
    #[Route('/produit/{id}', name: 'image_par_produit', methods: ['GET'])]
    public function imagesParProduit(
        int $id,
        ImageRepository $repo,
        ProduitRepository $produitRepo
    ): JsonResponse {
        $produit = $produitRepo->find($id);
        if (!$produit) {
            return $this->json(['error' => 'Produit introuvable'], 404);
        }

        $images = $repo->findBy(['produit' => $produit]);

        return $this->json(array_map(function (Image $image) {
            return [
                'id' => $image->getId(),
                'lien' => $image->getLien(),
                'produit' => $image->getProduit()->getId()
            ];
        }, $images));
    }

    // Supprimer des images d’un produit
    #[Route('/supprimer', name: 'image_supprimer', methods: ['POST'])]
    public function supprimerImages(
        Request $request,
        EntityManagerInterface $em,
        ImageRepository $repo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];

        if (!is_array($ids) || empty($ids)) {
            return $this->json(['error' => 'Aucun ID d’image fourni'], 400);
        }

        foreach ($ids as $id) {
            $image = $repo->find($id);
            if ($image) {
                $em->remove($image);
            }
        }

        $em->flush();

        return $this->json(['message' => 'Images supprimées']);
    }
}
